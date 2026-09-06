<?php

declare(strict_types=1);

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\User;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\CashFlowService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Assets\AssetReconciliationService;
use App\Services\Assets\AssetService;
use App\Services\Assistant\AccountingAssistantService;
use App\Services\Budget\BudgetService;
use App\Services\Consolidation\ConsolidationService;
use App\Services\Cost\CostAllocationService;
use App\Services\Gl\ManualJournalService;
use App\Services\Inventory\InventoryReconciliationService;
use App\Services\Inventory\InventoryService;
use App\Services\Lease\LeaseService;
use App\Services\Payroll\PayrollService;
use App\Services\Revenue\RevenueRecognitionService;
use App\Services\Risk\RiskService;
use App\Services\Security\AccessControl;
use App\Services\Tax\TaxEngine;
use App\Services\Tax\TaxService;
use App\Services\Treasury\LoanService;
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
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();
});

it('computes configured VAT and splits input/output on documents', function () {
    $engine = app(TaxEngine::class);
    $code = $engine->defineCode($this->company, [
        'code' => 'VAT-OUT',
        'name' => 'Output VAT',
        'kind' => 'output_vat',
        'gl_account_code' => '210404',
    ]);
    $engine->approveRate($code, [
        'rate' => '2',
        'effective_from' => '2026-01-01',
        'legal_reference' => 'LY-VAT-DEMO-2PCT',
        'authority' => 'LRA',
    ]);

    $computed = $engine->compute($this->company, 'VAT-OUT', '1000', '2026-03-10');
    expect((float) $computed['tax'])->toBe(20.0);

    $customer = app(CustomerService::class)->create($this->company, ['code' => 'TAX-C', 'name_ar' => 'عميل ضريبة']);
    $invoice = app(SalesInvoiceService::class)->post(app(SalesInvoiceService::class)->createDraft(
        $this->company,
        $this->book,
        $customer,
        [['revenue_account' => '410101', 'net' => 1000, 'tax_code' => 'VAT-OUT']],
        ['invoice_date' => '2026-03-10'],
    ));
    $j = $invoice->journal()->with('lines')->first();
    expect((float) $j->lines->firstWhere('account_id', ($this->acc)('210404')->id)->credit)->toBe(20.0)
        ->and((float) $invoice->tax_total)->toBe(20.0);

    app(TaxService::class)->withhold($this->company, '2026-03-11', '210101', '210403', '50');
    app(TaxService::class)->postDeferred($this->company, '2026-03-12', '30');
    $settle = app(TaxService::class)->settleVat($this->company, $this->book, '2026-03-31');
    expect($settle->isBalanced())->toBeTrue();
    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('rejects a tax rate without legal reference', function () {
    $engine = app(TaxEngine::class);
    $code = $engine->defineCode($this->company, [
        'code' => 'VAT-X',
        'name' => 'X',
        'kind' => 'output_vat',
        'gl_account_code' => '210404',
    ]);
    $engine->approveRate($code, ['rate' => '5', 'effective_from' => '2026-01-01', 'legal_reference' => '']);
})->throws(PostingException::class);

it('capitalizes depreciates impairs transfers and disposes a fixed asset (INV-10)', function () {
    $assets = app(AssetService::class);
    $asset = $assets->acquire($this->company, $this->book, [
        'code' => 'FA-1',
        'name' => 'Machine',
        'cost' => 12000,
        'useful_life_months' => 12,
        'in_service_date' => '2026-01-31',
    ]);
    $assets->depreciate($asset, '2026-02-28');
    $assets->impair($asset->fresh(), '2026-02-28', 100);
    $assets->transfer($asset->fresh(), 'Plant-A');
    expect($asset->fresh()->location)->toBe('Plant-A');
    app(AssetReconciliationService::class)->assert($this->company, $this->book);

    $assets->dispose($asset->fresh(), '2026-03-15', 10900);
    app(AssetReconciliationService::class)->assert($this->company, $this->book);
    expect(app(TrialBalanceService::class)->totals($this->company, $this->book)['balanced'])->toBeTrue();
});

it('receives issues transfers and adjusts inventory keeping INV-9', function () {
    $inv = app(InventoryService::class);
    $item = $inv->defineItem($this->company, ['code' => 'SKU-1', 'name' => 'Widget']);
    $other = $inv->defineItem($this->company, ['code' => 'SKU-1', 'name' => 'Widget', 'warehouse_code' => 'WH2']);
    $inv->receive($item, '2026-03-01', 10, 8);
    $inv->issue($item->fresh(), '2026-03-05', 2);
    $inv->transfer($item->fresh(), $other, '2026-03-06', 3);
    $inv->adjust($item->fresh(), '2026-03-07', -1);
    expect((float) $item->fresh()->quantity)->toBe(4.0);
    $check = app(InventoryReconciliationService::class)->assert($this->company, $this->book);
    expect($check->passed())->toBeTrue();
});

it('defers cash and recognizes IFRS 15 revenue over time', function () {
    $rev = app(RevenueRecognitionService::class);
    $contract = $rev->defer($this->company, $this->book, [
        'number' => 'SUB-1',
        'amount' => 900,
        'periods' => 3,
        'start_date' => '2026-01-01',
    ]);
    $rev->recognize($contract, '2026-01-31');
    $rev->recognize($contract->fresh(), '2026-02-28');
    $done = $rev->recognize($contract->fresh(), '2026-03-31');
    expect($done->status)->toBe('completed')->and((float) $done->recognized)->toBe(900.0);
});

it('accounts for IFRS 16 lease commencement interest payment and ROU depreciation', function () {
    $leases = app(LeaseService::class);
    $lease = $leases->commence($this->company, $this->book, [
        'code' => 'L-1',
        'present_value' => 12000,
        'interest_rate' => 1,
        'term_months' => 12,
        'commencement_date' => '2026-01-01',
    ]);
    $leases->chargeInterest($lease, '2026-01-31');
    $leases->pay($lease->fresh(), '2026-01-31', 1100);
    $leases->depreciate($lease->fresh(), '2026-01-31');
    expect((float) $lease->fresh()->accum_depreciation)->toBe(1000.0);
    expect(app(TrialBalanceService::class)->totals($this->company, $this->book)['balanced'])->toBeTrue();
});

it('draws down and repays a loan with interest', function () {
    $loans = app(LoanService::class);
    $loan = $loans->drawdown($this->company, $this->book, ['code' => 'LN-1', 'principal' => 10000, 'date' => '2026-03-01']);
    $loans->repay($loan, '2026-03-31', 2000, 500);
    expect((float) $loan->fresh()->outstanding)->toBe(8000.0);
});

it('accrues payroll liabilities from configured amounts and pays net', function () {
    $pay = app(PayrollService::class);
    $run = $pay->accrue($this->company, $this->book, [
        'run_date' => '2026-03-31',
        'gross' => 10000,
        'employer_ss' => 750,
        'paye' => 500,
        'employee_ss' => 375,
    ]);
    expect((float) $run->net)->toBe(9125.0);
    $pay->payNet($run, '2026-04-02');
    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('compares budget to actual and posts a cost allocation', function () {
    app(JournalService::class)->createAndPost($this->company, $this->book, [
        'journal_date' => '2026-03-10',
        'source' => 'manual',
        'is_system_generated' => true,
    ], [
        LineInput::debit(($this->acc)('620201')->id, 400),
        LineInput::credit(($this->acc)('110102')->id, 400),
    ]);
    $budget = app(BudgetService::class);
    $budget->set($this->company, $this->book, '2026-03', '620201', 500);
    $v = $budget->variance($this->company, $this->book, '2026-03', '620201', Carbon::parse('2026-03-31'));
    expect((float) $v['budget'])->toBe(500.0)->and((float) $v['actual'])->toBe(400.0)->and((float) $v['variance'])->toBe(100.0);

    app(CostAllocationService::class)->allocate($this->company, '2026-03-15', '620201', '620203', '50');
    $cf = app(CashFlowService::class)->summary($this->company, $this->book);
    expect($cf)->toHaveKeys(['inflows', 'outflows', 'net']);
});

it('eliminates intercompany balances on consolidation', function () {
    $j = app(ConsolidationService::class)->eliminateIntercompany($this->company, '2026-03-31', '110201', '210101', '250');
    expect($j->isBalanced())->toBeTrue();
});

it('enforces RBAC and SoD-1 on manual journals', function () {
    $creator = User::factory()->create();
    $approver = User::factory()->create();
    $access = app(AccessControl::class);
    $access->grant($approver, $this->company, 'journal.approve');
    $access->grant($approver, $this->company, 'journal.post');

    $manual = app(ManualJournalService::class);
    $draft = $manual->createDraft($this->company, $this->book, [
        'journal_date' => '2026-03-10',
        'created_by' => $creator->id,
    ], [
        LineInput::debit(($this->acc)('620201')->id, 80),
        LineInput::credit(($this->acc)('110102')->id, 80),
    ]);

    expect(fn () => $manual->approve($draft, $creator))->toThrow(PostingException::class);
    $manual->approve($draft->fresh(), $approver);
    expect(fn () => $manual->post($draft->fresh(), $creator))->toThrow(PostingException::class);
    expect($manual->post($draft->fresh(), $approver)->status->value)->toBe('posted');
});

it('rejects unauthorized period reopen', function () {
    $user = User::factory()->create();
    $period = FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 1)->firstOrFail();
    app(PeriodService::class)->hardClose($period);
    expect(fn () => app(PeriodService::class)->reopen($period->fresh(), 'need it', $user->id))
        ->toThrow(PostingException::class);
});

it('flags duplicate commercial invoices as risk', function () {
    $customer = app(CustomerService::class)->create($this->company, ['code' => 'RISK-C', 'name_ar' => 'ر']);
    $svc = app(SalesInvoiceService::class);
    $svc->createDraft($this->company, $this->book, $customer, [['revenue_account' => '410101', 'net' => 77]], ['invoice_date' => '2026-03-10', 'number' => 'A-1']);
    $svc->createDraft($this->company, $this->book, $customer, [['revenue_account' => '410101', 'net' => 77]], ['invoice_date' => '2026-03-10', 'number' => 'A-2']);
    $flags = app(RiskService::class)->scan($this->company);
    expect(collect($flags)->firstWhere('code', 'DUPLICATE_INVOICE'))->not->toBeNull();
});

it('posts an assistant draft only after review and approval', function () {
    $asst = app(AccountingAssistantService::class);
    $draft = $asst->propose($this->company, $this->book, 'accrue rent', [
        ['account_id' => ($this->acc)('620201')->id, 'debit' => 90],
        ['account_id' => ($this->acc)('210203')->id, 'credit' => 90],
    ]);
    expect(fn () => $asst->post($draft))->toThrow(PostingException::class);
    $asst->review($draft);
    $asst->approve($draft->fresh());
    $j = $asst->post($draft->fresh());
    expect($j->isBalanced())->toBeTrue();
});
