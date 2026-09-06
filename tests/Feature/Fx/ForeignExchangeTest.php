<?php

declare(strict_types=1);

use App\Enums\Accounting\RateType;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Fx\Exceptions\FxException;
use App\Services\Fx\ExchangeRateService;
use App\Services\Fx\FxRevaluationService;
use App\Services\Fx\FxTranslationService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail()->id;

    $this->rates = app(ExchangeRateService::class);
    $this->reval = app(FxRevaluationService::class);
    $this->translate = app(FxTranslationService::class);
    $this->customers = app(CustomerService::class);
    $this->invoices = app(SalesInvoiceService::class);
    $this->receipts = app(ReceiptService::class);

    $this->customer = $this->customers->create($this->company, [
        'code' => 'FX-C1',
        'name_ar' => 'عميل دولار',
        'currency' => 'USD',
    ]);
});

function fx_line($journal, callable $acc, string $code)
{
    return $journal->lines->firstWhere('account_id', $acc($code));
}

it('resolves exchange rates with prior-date fallback and converts amounts', function () {
    $this->rates->setRate($this->company, 'USD', 'LYD', '4.800000', '2026-03-01', RateType::SPOT, 'CBL');
    $this->rates->setRate($this->company, 'USD', 'LYD', '4.900000', '2026-03-31', RateType::CLOSING, 'CBL');

    expect((float) $this->rates->resolve($this->company, 'USD', 'LYD', '2026-03-15', RateType::SPOT))->toBe(4.8)
        ->and((float) $this->rates->resolve($this->company, 'USD', 'LYD', '2026-03-31', RateType::CLOSING))->toBe(4.9)
        ->and((float) $this->rates->convert($this->company, 1000, 'USD', 'LYD', '2026-03-31', RateType::CLOSING))->toBe(4900.0);

    // Inverse pair
    expect((float) $this->rates->resolve($this->company, 'LYD', 'USD', '2026-03-01', RateType::SPOT))
        ->toEqualWithDelta(1 / 4.8, 0.000001);
});

it('rejects missing rates', function () {
    $this->rates->resolve($this->company, 'EUR', 'LYD', '2026-03-01');
})->throws(FxException::class);

it('converts a foreign-currency invoice at the document rate (transaction currency)', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax' => 0],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'USD', 'exchange_rate' => 4.8]));

    $ij = $invoice->journal()->with('lines')->first();
    expect($ij->currency)->toBe('USD')
        ->and((float) fx_line($ij, $this->acc, '110201')->debit)->toBe(1000.0)
        ->and((float) fx_line($ij, $this->acc, '110201')->functional_debit)->toBe(4800.0);

    expect(app(TrialBalanceService::class)->totals($this->company, $this->book)['balanced'])->toBeTrue();
    app(ArReconciliationService::class)->assert($this->company, $this->book);
});

it('posts unrealized FX on period-end revaluation of open AR', function () {
    $this->rates->setRate($this->company, 'USD', 'LYD', '4.900000', '2026-03-31', RateType::CLOSING);

    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax' => 0],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'USD', 'exchange_rate' => 4.8]));

    $run = $this->reval->revalueOpenItems($this->company, $this->book, '2026-03-31');
    expect($run->lines)->toHaveCount(1)
        ->and((float) $run->total_gain)->toBe(100.0) // (4.9-4.8)*1000
        ->and((float) $invoice->fresh()->revaluation_rate)->toBe(4.9);

    // Unrealized gain journal: Dr AR 100 · Cr 420104
    $check = app(ArReconciliationService::class)->assert($this->company, $this->book);
    expect($check->passed())->toBeTrue()
        ->and((float) $check->expected)->toBe(4900.0);

    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('posts realized FX gain when settling AR at a higher rate', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax' => 0],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'USD', 'exchange_rate' => 4.8]));

    $receipt = $this->receipts->post($this->receipts->createDraft($this->company, $this->book, $this->customer, 1000, [
        'receipt_date' => '2026-03-25',
        'currency' => 'USD',
        'exchange_rate' => 4.9,
        'cash_bank_account' => '110102',
    ]));

    $alloc = $this->receipts->allocate($receipt, $invoice, 1000);
    expect($alloc->fx_journal_id)->not->toBeNull()
        ->and((float) $alloc->fx_amount)->toBe(100.0);

    $fxj = $alloc->fxJournal()->with('lines')->first();
    expect($fxj)->not->toBeNull()
        ->and((float) fx_line($fxj, $this->acc, '110201')->debit)->toBe(100.0)
        ->and((float) fx_line($fxj, $this->acc, '420104')->credit)->toBe(100.0);

    expect((float) $invoice->fresh()->openBalance())->toBe(0.0);
    app(ArReconciliationService::class)->assert($this->company, $this->book);
    expect(app(TrialBalanceService::class)->totals($this->company, $this->book)['balanced'])->toBeTrue();
});

it('posts translation residual to OCI when presentation differs from functional', function () {
    // Seed some equity/activity in functional currency
    app(JournalService::class)->createAndPost($this->company, $this->book, [
        'journal_date' => '2026-03-01',
        'source' => 'opening',
        'is_system_generated' => true,
    ], [
        LineInput::debit(($this->acc)('110102'), 10000),
        LineInput::credit(($this->acc)('310101'), 10000),
    ]);

    $this->company->forceFill(['presentation_currency' => 'USD'])->save();
    $this->rates->setRate($this->company, 'LYD', 'USD', '0.200000', '2026-03-31', RateType::CLOSING);

    $run = $this->translate->translate($this->company, $this->book, '2026-03-31', priorRate: '0.180000');
    expect($run->journal_id)->not->toBeNull()
        ->and((float) $run->difference)->not->toBe(0.0);

    $j = $run->journal()->with('lines')->first();
    expect(fx_line($j, $this->acc, '330102'))->not->toBeNull();

    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id, asOf: '2026-03-31'));
    expect($bs['balanced'])->toBeTrue();
    expect(app(IntegrityService::class)->check($this->company, $this->book, Carbon::parse('2026-03-31'))['passed'])->toBeTrue();
});
