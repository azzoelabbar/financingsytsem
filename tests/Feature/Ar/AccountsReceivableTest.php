<?php

declare(strict_types=1);

use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ar\ArLedgerService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Ar\BadDebtWriteOffService;
use App\Services\Ar\CollectionService;
use App\Services\Ar\CreditNoteService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\DebitNoteService;
use App\Services\Ar\EclService;
use App\Services\Ar\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ar\Exceptions\CurrencyMismatchException;
use App\Services\Ar\Exceptions\DuplicateDocumentException;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
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

    $this->customers = app(CustomerService::class);
    $this->invoices = app(SalesInvoiceService::class);
    $this->receipts = app(ReceiptService::class);
    $this->creditNotes = app(CreditNoteService::class);
    $this->ledger = app(ArLedgerService::class);
    $this->recon = app(ArReconciliationService::class);

    $this->customer = $this->customers->create($this->company, ['code' => 'C-001', 'name_ar' => 'عميل تجريبي']);
});

function jline($journal, callable $acc, string $code)
{
    return $journal->lines->firstWhere('account_id', $acc($code));
}

it('runs the full AR lifecycle keeping every invariant', function () {
    // --- Invoice: Dr AR 1050 / Cr Revenue 1000 / Cr Output VAT 50 ---
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax_account' => '210404', 'tax' => 50],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-04-09']);
    $invoice = $this->invoices->post($invoice);

    expect($invoice->status)->toBe(DocumentStatus::POSTED)
        ->and($invoice->journal_id)->not->toBeNull()
        ->and($invoice->number)->not->toBeNull();

    $ij = $invoice->journal()->with('lines')->first();
    expect($ij->isBalanced())->toBeTrue()
        ->and((float) jline($ij, $this->acc, '110201')->debit)->toBe(1050.0)
        ->and((float) jline($ij, $this->acc, '410101')->credit)->toBe(1000.0)
        ->and((float) jline($ij, $this->acc, '210404')->credit)->toBe(50.0);

    // --- Partial receipt 600: Dr Bank / Cr AR, then allocate to the invoice ---
    $receipt = $this->receipts->createDraft($this->company, $this->book, $this->customer, 600, ['receipt_date' => '2026-03-20', 'cash_bank_account' => '110102']);
    $receipt = $this->receipts->post($receipt);
    $rj = $receipt->journal()->with('lines')->first();
    expect((float) jline($rj, $this->acc, '110102')->debit)->toBe(600.0)
        ->and((float) jline($rj, $this->acc, '110201')->credit)->toBe(600.0);

    $this->receipts->allocate($receipt, $invoice, 600);
    expect((float) $invoice->fresh()->openBalance())->toBe(450.0)
        ->and((float) $receipt->fresh()->unallocated_amount)->toBe(0.0);

    // --- Aging + subledger = control (INV-7) ---
    $aging = $this->ledger->aging($this->company);
    expect((float) $aging['total'])->toBe(450.0)
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(450.0);

    $check = $this->recon->assert($this->company, $this->book);
    expect($check->passed())->toBeTrue()
        ->and((float) $check->expected)->toBe(450.0); // GL 110201 balance

    // --- Credit note 200: Dr Revenue / Cr AR ---
    $cn = $this->creditNotes->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 200, 'tax' => 0],
    ], ['credit_note_date' => '2026-03-25', 'sales_invoice_id' => $invoice->id, 'reason' => 'خصم']);
    $cn = $this->creditNotes->post($cn);
    $cnj = $cn->journal()->with('lines')->first();
    expect((float) jline($cnj, $this->acc, '410101')->debit)->toBe(200.0)
        ->and((float) jline($cnj, $this->acc, '110201')->credit)->toBe(200.0);

    // GL AR now 1050 - 600 - 200 = 250; subledger must match.
    $check2 = $this->recon->assert($this->company, $this->book);
    expect((float) $check2->expected)->toBe(250.0)
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(250.0)
        ->and((float) $invoice->fresh()->openBalance())->toBe(250.0)
        ->and((float) $this->ledger->aging($this->company)['total'])->toBe(250.0);

    // --- Core invariants + financial statements balanced ---
    $integrity = app(IntegrityService::class)->check($this->company, $this->book);
    expect($integrity['passed'])->toBeTrue();

    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id));
    expect($bs['balanced'])->toBeTrue();
});

it('keeps posted invoices immutable (no re-post)', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);
    $invoice = $this->invoices->post($invoice);

    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('keeps an invoice current throughout its due date', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-03-10']);
    $this->invoices->post($invoice);

    $aging = $this->ledger->aging($this->company, Carbon::parse('2026-03-10 23:59:59'));

    expect((float) $aging['buckets']['current'])->toBe(100.0)
        ->and((float) $aging['buckets']['1_30'])->toBe(0.0);
});

it('rejects a duplicate invoice number', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10', 'number' => 'INV-DUP']);

    $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10', 'number' => 'INV-DUP']);
})->throws(DuplicateDocumentException::class);

it('rejects an allocation that exceeds the invoice open balance', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);
    $invoice = $this->invoices->post($invoice);

    $receipt = $this->receipts->post($this->receipts->createDraft($this->company, $this->book, $this->customer, 500, ['receipt_date' => '2026-03-20']));

    $this->receipts->allocate($receipt, $invoice, 200); // invoice open is only 100
})->throws(AllocationExceedsBalanceException::class);

it('refuses to post into a hard-closed period', function () {
    FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 3)->update(['status' => 'hard_closed']);

    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);

    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('rejects an invalid revenue account at posting', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '999999', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);

    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('posts a debit note as Dr AR / Cr Revenue / Cr Output VAT', function () {
    $dn = app(DebitNoteService::class)->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 200, 'tax_account' => '210404', 'tax' => 10],
    ], ['debit_note_date' => '2026-03-12']);
    $dn = app(DebitNoteService::class)->post($dn);

    expect($dn->status)->toBe(DocumentStatus::POSTED)->and($dn->journal_id)->not->toBeNull();

    $j = $dn->journal()->with('lines')->first();
    expect($j->isBalanced())->toBeTrue()
        ->and((float) jline($j, $this->acc, '110201')->debit)->toBe(210.0)
        ->and((float) jline($j, $this->acc, '410101')->credit)->toBe(200.0)
        ->and((float) jline($j, $this->acc, '210404')->credit)->toBe(10.0);

    $this->recon->assert($this->company, $this->book);
    expect((float) $this->ledger->subledgerTotal($this->company))->toBe(210.0);
});

it('converts a foreign-currency invoice into functional currency at the document rate', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax_account' => '210404', 'tax' => 50],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'USD', 'exchange_rate' => 4.8]);
    $invoice = $this->invoices->post($invoice);

    $ij = $invoice->journal()->with('lines')->first();
    $ar = jline($ij, $this->acc, '110201');
    expect($ij->currency)->toBe('USD')
        ->and((float) $ar->debit)->toBe(1050.0)
        ->and((float) $ar->functional_debit)->toBe(5040.0) // 1050 × 4.8
        ->and($ij->isBalanced())->toBeTrue();

    expect((float) $this->ledger->subledgerTotal($this->company))->toBe(5040.0);
    $this->recon->assert($this->company, $this->book);

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
});

it('rejects posting when a mandatory dimension is missing', function () {
    Account::where('company_id', $this->company->id)->where('code', '410101')->update(['requires_cost_center' => true]);

    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);

    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('posts when the required dimension is supplied on the line', function () {
    Account::where('company_id', $this->company->id)->where('code', '410101')->update(['requires_cost_center' => true]);

    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100, 'dimensions' => ['COST_CENTER' => 'SAL']],
    ], ['invoice_date' => '2026-03-10']);
    $invoice = $this->invoices->post($invoice);

    expect($invoice->status)->toBe(DocumentStatus::POSTED);
    $this->recon->assert($this->company, $this->book);
});

it('rejects a zero-amount invoice', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 0],
    ], ['invoice_date' => '2026-03-10']);
})->throws(PostingException::class);

it('posts on fiscal-period boundary dates', function () {
    $first = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 10],
    ], ['invoice_date' => '2026-01-01']));
    $last = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 15],
    ], ['invoice_date' => '2026-12-31']));

    expect($first->status)->toBe(DocumentStatus::POSTED)
        ->and($last->status)->toBe(DocumentStatus::POSTED);

    $this->recon->assert($this->company, $this->book);
});

it('rejects allocating a receipt in a different currency', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'LYD', 'exchange_rate' => 1]));

    $receipt = $this->receipts->post($this->receipts->createDraft($this->company, $this->book, $this->customer, 100, [
        'receipt_date' => '2026-03-20',
        'currency' => 'USD',
        'exchange_rate' => 1,
    ]));

    $this->receipts->allocate($receipt, $invoice, 100);
})->throws(CurrencyMismatchException::class);

it('writes off a receivable covered by the loss allowance', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 400],
    ], ['invoice_date' => '2026-03-10']));

    $writeoff = app(BadDebtWriteOffService::class)->writeOff(
        $this->company,
        $this->book,
        $this->customer,
        400,
        ['sales_invoice_id' => $invoice->id, 'covered_by_allowance' => true, 'writeoff_date' => '2026-03-28', 'reason' => 'إفلاس'],
    );

    $wj = $writeoff->journal()->with('lines')->first();
    expect($writeoff->journal_id)->not->toBeNull()
        ->and((float) jline($wj, $this->acc, '110204')->debit)->toBe(400.0)
        ->and((float) jline($wj, $this->acc, '110201')->credit)->toBe(400.0)
        ->and((float) $invoice->fresh()->openBalance())->toBe(0.0);

    $this->recon->assert($this->company, $this->book);
    expect((float) $this->ledger->subledgerTotal($this->company))->toBe(0.0);
});

it('writes off an uncovered receivable to bad-debt expense', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 150],
    ], ['invoice_date' => '2026-03-10']));

    $writeoff = app(BadDebtWriteOffService::class)->writeOff(
        $this->company,
        $this->book,
        $this->customer,
        150,
        ['sales_invoice_id' => $invoice->id, 'covered_by_allowance' => false, 'writeoff_date' => '2026-03-28'],
    );

    $wj = $writeoff->journal()->with('lines')->first();
    expect((float) jline($wj, $this->acc, '630201')->debit)->toBe(150.0)
        ->and((float) jline($wj, $this->acc, '110201')->credit)->toBe(150.0);

    $this->recon->assert($this->company, $this->book);
});

it('posts an IFRS 9 ECL allowance without moving the AR control', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 1000],
    ], ['invoice_date' => '2026-03-10']));

    $ecl = app(EclService::class)->postAssessment($this->company, $this->book, [
        'as_of_date' => '2026-03-31',
        'stage' => 2,
        'gross_exposure' => 1000,
        'loss_rate' => '0.05',
        'allowance_amount' => 50,
        'method' => 'provision-matrix-v1',
        'customer_id' => $this->customer->id,
    ]);

    $ej = $ecl->journal()->with('lines')->first();
    expect($ecl->journal_id)->not->toBeNull()
        ->and((float) jline($ej, $this->acc, '630203')->debit)->toBe(50.0)
        ->and((float) jline($ej, $this->acc, '110204')->credit)->toBe(50.0)
        ->and((float) $invoice->fresh()->openBalance())->toBe(1000.0);

    // INV-7: AR control is untouched by the ECL journal.
    $check = $this->recon->assert($this->company, $this->book);
    expect((float) $check->expected)->toBe(1000.0);
});

it('builds a customer statement whose balance equals aging and the AR control', function () {
    $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 300],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-04-09']));

    $statement = $this->ledger->statement($this->customer);
    expect($statement['customer']['code'])->toBe('C-001')
        ->and($statement['open_items'])->toHaveCount(1)
        ->and((float) $statement['balance'])->toBe(300.0)
        ->and((float) $statement['aging']['total'])->toBe(300.0);

    $this->recon->assert($this->company, $this->book);
});

it('keeps an overpayment as an unallocated advance reconciling to the AR control', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']));

    $receipt = $this->receipts->post($this->receipts->createDraft($this->company, $this->book, $this->customer, 250, [
        'receipt_date' => '2026-03-20',
        'cash_bank_account' => '110102',
    ]));
    $this->receipts->allocate($receipt, $invoice, 100);

    expect((float) $invoice->fresh()->openBalance())->toBe(0.0)
        ->and((float) $receipt->fresh()->unallocated_amount)->toBe(150.0)
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(-150.0);

    $this->recon->assert($this->company, $this->book);
});

it('records a collection activity without touching the ledger', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->customer, [
        ['revenue_account' => '410101', 'net' => 80],
    ], ['invoice_date' => '2026-03-10']));

    $activity = app(CollectionService::class)->record($this->company, $this->customer, [
        'type' => 'promise',
        'activity_date' => '2026-03-22',
        'sales_invoice_id' => $invoice->id,
        'promise_to_pay_date' => '2026-04-01',
        'dunning_level' => 1,
        'notes' => 'وعد بالسداد',
    ]);

    expect($activity->type)->toBe('promise')
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(80.0);

    $this->recon->assert($this->company, $this->book);
});

it('classifies a customer with a credit profile, group, and payment terms', function () {
    $group = $this->customers->createGroup($this->company, ['code' => 'G-TRADE', 'name_ar' => 'تجاري']);
    $terms = $this->customers->createPaymentTerm($this->company, ['code' => 'NET30', 'name' => 'Net 30', 'net_days' => 30]);
    $this->customers->setCreditProfile($this->customer, [
        'credit_limit' => 50000,
        'payment_terms_id' => $terms->id,
        'risk_band' => 'B',
    ]);
    $this->customers->addContact($this->customer, ['name' => 'أحمد', 'is_primary' => true]);
    $this->customers->addAddress($this->customer, ['type' => 'billing', 'line1' => 'طرابلس', 'is_primary' => true]);

    $this->customer->refresh();
    expect($this->customer->creditProfile->risk_band)->toBe('B')
        ->and((float) $this->customer->creditProfile->credit_limit)->toBe(50000.0)
        ->and($group->code)->toBe('G-TRADE')
        ->and($this->customer->contacts)->toHaveCount(1)
        ->and($this->customer->addresses)->toHaveCount(1);
});
