<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Ar\SalesInvoice;
use App\Models\Gl\OpeningBalanceBatch;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ap\ApReconciliationService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Gl\OpeningBalanceService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();
});

it('enforces INV-1 through INV-12 cross-module invariants', function () {
    $journals = app(JournalService::class);
    $ob = app(OpeningBalanceService::class)->post($this->company, $this->book, '2026-01-01', [
        ['account' => '110102', 'debit' => 10000],
        ['account' => '310101', 'credit' => 10000],
    ]);
    expect($ob->journal->isBalanced())->toBeTrue(); // INV-1 / INV-12

    $customer = app(CustomerService::class)->create($this->company, ['code' => 'INV-C', 'name_ar' => 'عميل']);
    $invoice = app(SalesInvoiceService::class)->post(app(SalesInvoiceService::class)->createDraft(
        $this->company,
        $this->book,
        $customer,
        [['revenue_account' => '410101', 'net' => 1000]],
        ['invoice_date' => '2026-02-10', 'number' => 'INV-G-1'],
    ));
    expect($invoice->journal_id)->not->toBeNull(); // INV-3
    $journal = $invoice->journal;
    expect($journal->status)->toBe(JournalStatus::POSTED);
    expect(fn () => app(JournalService::class)->post($journal))->toThrow(PostingException::class); // INV-4 posted immutable

    $period = FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 3)->firstOrFail();
    app(PeriodService::class)->hardClose($period);
    expect(fn () => $journals->createAndPost($this->company, $this->book, [
        'journal_date' => '2026-03-15',
    ], [
        LineInput::debit(($this->acc)('620201')->id, 1),
        LineInput::credit(($this->acc)('110102')->id, 1),
    ]))->toThrow(PostingException::class); // INV-5

    app(ArReconciliationService::class)->assert($this->company, $this->book); // INV-7
    app(ApReconciliationService::class)->assert($this->company, $this->book); // INV-8

    $orphanLines = DB::table('journal_lines as jl')
        ->leftJoin('journals as j', 'j.id', '=', 'jl.journal_id')
        ->whereNull('j.id')
        ->count();
    expect($orphanLines)->toBe(0); // INV-9

    $orphanDocs = SalesInvoice::query()
        ->where('company_id', $this->company->id)
        ->where('status', DocumentStatus::POSTED)
        ->whereNull('journal_id')
        ->count();
    expect($orphanDocs)->toBe(0); // INV-10

    $integrity = app(IntegrityService::class)->check($this->company, $this->book);
    expect($integrity['passed'])->toBeTrue(); // INV-1, INV-2, INV-3

    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest(
        companyId: $this->company->id,
        bookId: $this->book->id,
    ));
    expect($bs['balanced'])->toBeTrue(); // INV-2 / INV-11 book internal consistency

    $batches = OpeningBalanceBatch::query()->where('company_id', $this->company->id)->where('status', 'posted')->get();
    foreach ($batches as $batch) {
        expect($batch->journal->isBalanced())->toBeTrue(); // INV-12
    }

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
});
