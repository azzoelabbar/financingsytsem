<?php

declare(strict_types=1);

use App\Enums\Accounting\BookBasis;
use App\Enums\Accounting\JournalStatus;
use App\Enums\Accounting\RateType;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\MultiBookService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\Reporting\ReportingPackService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ap\ApLedgerService;
use App\Services\Ap\ApReconciliationService;
use App\Services\Ap\PurchaseInvoiceService;
use App\Services\Ap\SupplierPaymentService;
use App\Services\Ap\SupplierService;
use App\Services\Ar\ArLedgerService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Ar\CreditNoteService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Expense\ExpenseClaimService;
use App\Services\Expense\ExpenseReimbursementService;
use App\Services\Fx\ExchangeRateService;
use App\Services\Gl\OpeningBalanceService;
use App\Services\Investment\InvestmentService;
use App\Services\Localization\CountryPackService;
use App\Services\Project\ProjectService;
use App\Services\Tax\TaxEngine;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('runs the production end-to-end accounting scenario across AR AP FX investments expenses projects tax opening balances multi-book and reports', function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $company = Company::firstOrFail();
    $local = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $ifrs = AccountingBook::where('company_id', $company->id)->where('code', 'IFRS')->firstOrFail();
    $taxBook = AccountingBook::where('company_id', $company->id)->where('code', 'TAX')->firstOrFail();
    $acc = fn (string $code) => Account::where('company_id', $company->id)->where('code', $code)->firstOrFail();

    // Opening balance
    $ob = app(OpeningBalanceService::class)->post($company, $local, '2026-01-01', [
        ['account' => '110102', 'debit' => 50000],
        ['account' => '310101', 'credit' => 50000],
    ]);
    expect($ob->journal->isBalanced())->toBeTrue()->and($ob->journal->status)->toBe(JournalStatus::POSTED);

    // FX rates
    $rates = app(ExchangeRateService::class);
    $rates->setRate($company, 'USD', 'LYD', '4.800000', '2026-03-01', RateType::SPOT, 'CBL');

    // AR: USD invoice → partial receipt → allocation → FX → credit note → aging → recon
    $customer = app(CustomerService::class)->create($company, [
        'code' => 'E2E-C', 'name_ar' => 'عميل E2E', 'currency' => 'USD',
    ]);
    $invoice = app(SalesInvoiceService::class)->post(app(SalesInvoiceService::class)->createDraft(
        $company, $local, $customer,
        [['revenue_account' => '410101', 'net' => 1000, 'tax' => 0]],
        ['invoice_date' => '2026-03-10', 'currency' => 'USD', 'exchange_rate' => 4.8, 'number' => 'E2E-INV-1'],
    ));
    expect($invoice->journal_id)->not->toBeNull()->and($invoice->journal->isBalanced())->toBeTrue();

    $receipt = app(ReceiptService::class)->post(app(ReceiptService::class)->createDraft(
        $company, $local, $customer, 400,
        ['receipt_date' => '2026-03-20', 'currency' => 'USD', 'exchange_rate' => 4.9, 'number' => 'E2E-RCP-1'],
    ));
    app(ReceiptService::class)->allocate($receipt, $invoice, 400);
    expect((float) $invoice->fresh()->openBalance())->toBe(600.0);

    $cn = app(CreditNoteService::class)->post(app(CreditNoteService::class)->createDraft(
        $company, $local, $customer,
        [['revenue_account' => '410101', 'net' => 100, 'tax' => 0]],
        ['credit_note_date' => '2026-03-25', 'currency' => 'USD', 'exchange_rate' => 4.8, 'number' => 'E2E-CN-1'],
    ));
    app(CreditNoteService::class)->allocate($cn, $invoice->fresh(), 100);
    expect($cn->journal_id)->not->toBeNull();

    $aging = app(ArLedgerService::class)->aging($company, Carbon::now());
    expect($aging)->toHaveKeys(['buckets', 'total']);
    app(ArReconciliationService::class)->assert($company, $local);

    // AP: supplier → purchase invoice → payment → recon
    $supplier = app(SupplierService::class)->create($company, [
        'code' => 'E2E-S', 'legal_name' => 'مورد E2E', 'currency' => 'LYD',
    ]);
    $bill = app(PurchaseInvoiceService::class)->post(app(PurchaseInvoiceService::class)->createDraft(
        $company, $local, $supplier,
        [['expense_account' => '620201', 'net' => 500, 'tax' => 0]],
        ['invoice_date' => '2026-03-12', 'supplier_invoice_number' => 'SUP-E2E-1'],
    ));
    expect($bill->journal_id)->not->toBeNull()->and($bill->journal->isBalanced())->toBeTrue();

    $payment = app(SupplierPaymentService::class)->post(app(SupplierPaymentService::class)->createDraft(
        $company, $local, $supplier, 200,
        ['payment_date' => '2026-03-18', 'number' => 'E2E-PAY-1'],
    ));
    app(SupplierPaymentService::class)->allocate($payment, $bill, 200);
    app(ApReconciliationService::class)->assert($company, $local);
    expect(app(ApLedgerService::class)->aging($company))->toHaveKeys(['buckets', 'total']);

    // Investments / expenses / projects
    $inv = app(InvestmentService::class)->acquire($company, $local, [
        'code' => 'E2E-INV', 'classification' => 'FVTPL', 'cost' => 1000, 'date' => '2026-03-05',
    ]);
    app(InvestmentService::class)->revalue($inv, '2026-03-28', 1050);
    expect($inv->fresh()->journal_id)->not->toBeNull();

    $claim = app(ExpenseClaimService::class)->createDraft($company, $local, [
        'number' => 'E2E-EXP', 'claim_date' => '2026-03-08',
    ], [['amount' => 75, 'expense_account' => '620201']]);
    app(ExpenseClaimService::class)->submit($claim);
    app(ExpenseClaimService::class)->approve($claim->fresh());
    $postedClaim = app(ExpenseClaimService::class)->post($claim->fresh());
    app(ExpenseReimbursementService::class)->reimburse($postedClaim, '2026-03-09');
    expect($postedClaim->fresh()->journal_id)->not->toBeNull();

    $project = app(ProjectService::class)->define($company, ['code' => 'E2E-P', 'budget' => 5000]);
    app(ProjectService::class)->charge($project, '2026-03-14', 300);
    app(ProjectService::class)->capitalize($project->fresh(), '2026-03-21', 150);

    // VAT (configured tax)
    $tax = app(TaxEngine::class);
    $code = $tax->defineCode($company, [
        'code' => 'E2E-VAT', 'name' => 'VAT', 'kind' => 'output_vat', 'gl_account_code' => '210404',
    ]);
    $tax->approveRate($code, [
        'rate' => '2', 'effective_from' => '2026-01-01', 'legal_reference' => 'E2E-VAT-LAW',
    ]);
    app(CountryPackService::class)->publishRule([
        'country' => 'LY', 'rule_code' => 'LY_VAT_STANDARD', 'rule_type' => 'tax_rate',
        'name_ar' => 'VAT', 'authority' => 'TEST', 'legal_reference' => 'E2E-VAT-LAW',
        'rate' => '2', 'effective_from' => '2026-01-01', 'status' => 'active',
    ]);

    // Multi-book isolation
    $multi = app(MultiBookService::class);
    $books = $multi->postParallel($company, 'cost.allocate', '2026-03-22', 'LYD', 'E2E-MB', [
        ['account' => '620203', 'debit' => 40],
        ['account' => '110102', 'credit' => 40],
    ], [BookBasis::LOCAL, BookBasis::IFRS, BookBasis::TAX]);
    expect($books)->toHaveCount(3)
        ->and($books[0]->book_id)->toBe($local->id)
        ->and($books[1]->book_id)->toBe($ifrs->id)
        ->and($books[2]->book_id)->toBe($taxBook->id);

    $localTb = app(TrialBalanceService::class)->totals($company, $local);
    $ifrsTb = app(TrialBalanceService::class)->totals($company, $ifrs);
    $taxTb = app(TrialBalanceService::class)->totals($company, $taxBook);
    expect($localTb['balanced'])->toBeTrue()
        ->and($ifrsTb['balanced'])->toBeTrue()
        ->and($taxTb['balanced'])->toBeTrue();

    // Reports
    $request = new ReportRequest(companyId: $company->id, bookId: $local->id);
    $bs = app(FinancialStatementService::class)->balanceSheet($request);
    $pl = app(FinancialStatementService::class)->incomeStatement($request);
    $pack = app(ReportingPackService::class)->managementPack($company, $local, $request);
    expect($bs['balanced'])->toBeTrue()
        ->and($pl)->toHaveKeys(['revenue', 'expenses', 'net_profit'])
        ->and($pack['cash_flow'])->toHaveKeys(['operating', 'investing', 'financing', 'lines'])
        ->and($pack['book_basis'])->toBe('local');

    // Integrity close-out
    expect(app(IntegrityService::class)->check($company, $local)['passed'])->toBeTrue();
    app(ArReconciliationService::class)->assert($company, $local);
    app(ApReconciliationService::class)->assert($company, $local);

    // Every posted document has a journal
    expect($invoice->fresh()->journal_id)->not->toBeNull()
        ->and($receipt->fresh()->journal_id)->not->toBeNull()
        ->and($cn->fresh()->journal_id)->not->toBeNull()
        ->and($bill->fresh()->journal_id)->not->toBeNull()
        ->and($payment->fresh()->journal_id)->not->toBeNull()
        ->and($postedClaim->fresh()->journal_id)->not->toBeNull();

    // Posted journal immutable
    expect(fn () => app(JournalService::class)->post($invoice->journal))
        ->toThrow(PostingException::class);

    // Locked period rejects posting
    $period = FiscalPeriod::where('company_id', $company->id)->where('period_no', 3)->firstOrFail();
    app(PeriodService::class)->hardClose($period);
    expect(fn () => app(InvestmentService::class)->acquire($company, $local, [
        'code' => 'E2E-CLOSED', 'classification' => 'FVTPL', 'cost' => 10, 'date' => '2026-03-16',
    ]))->toThrow(PostingException::class);

    // Book isolation: IFRS TB must not include LOCAL-only AR invoice amounts as if shared
    $ifrsAr = app(TrialBalanceService::class)->build($company, $ifrs)->firstWhere('code', '110201');
    $localAr = app(TrialBalanceService::class)->build($company, $local)->firstWhere('code', '110201');
    expect($localAr)->not->toBeNull();
    // AR invoice posted only to LOCAL primary book via AR services
    expect($ifrsAr === null || (float) $ifrsAr->balance !== (float) $localAr->balance || (float) $ifrsAr->balance === 0.0)->toBeTrue();

    $posted = Journal::query()->where('company_id', $company->id)->where('status', JournalStatus::POSTED)->count();
    expect($posted)->toBeGreaterThan(10);
});
