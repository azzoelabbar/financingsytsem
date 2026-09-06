<?php

declare(strict_types=1);

use App\Application\Api\Ap\ApApplicationService;
use App\Application\Api\Ar\ArApplicationService;
use App\Enums\Ar\DocumentStatus;
use App\Livewire\Ar\CreditNoteIndex;
use App\Livewire\Ar\CreditNoteShow;
use App\Livewire\Ar\CustomerCreate;
use App\Livewire\Ar\CustomerIndex;
use App\Livewire\Ar\DebitNoteIndex;
use App\Livewire\Ar\DebitNoteShow;
use App\Livewire\Ar\NoteCreate;
use App\Livewire\Ar\OpenItems;
use App\Livewire\Ar\ReceiptCreate;
use App\Livewire\Ar\ReceiptShow;
use App\Livewire\Ar\Reconciliation;
use App\Livewire\Ar\SalesInvoiceCreate;
use App\Livewire\Ar\SalesInvoiceIndex;
use App\Livewire\Ar\SalesInvoiceShow;
use App\Livewire\Ar\Statement;
use App\Livewire\Assets\DepreciationRegister;
use App\Livewire\Assets\FixedAssetIndex;
use App\Livewire\Banking\ReconciliationIndex;
use App\Livewire\Banking\TransactionIndex;
use App\Livewire\Banking\TreasuryAccounts;
use App\Livewire\Expenses\Index;
use App\Livewire\Gl\AccountBalances;
use App\Livewire\Gl\AccountIndex;
use App\Livewire\Gl\GeneralLedger;
use App\Livewire\Layout\ContextBar;
use App\Livewire\Reports\BalanceSheet;
use App\Livewire\Reports\BudgetVsActual;
use App\Livewire\Reports\CashFlow;
use App\Livewire\Reports\CashForecast;
use App\Livewire\Reports\ManagementPack;
use App\Livewire\Reports\Oci;
use App\Livewire\Tax\RuleIndex;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesDebitNote;
use App\Models\Ar\SalesInvoice;
use App\Models\Tax\TaxCode;
use App\Models\User;
use App\Services\Ar\CreditNoteService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());
});

test('switching the accounting book asks the browser to refresh the current workspace', function () {
    $company = Company::firstOrFail();
    $ifrs = AccountingBook::query()
        ->where('company_id', $company->id)
        ->where('code', 'IFRS')
        ->firstOrFail();

    Livewire::test(ContextBar::class)
        ->call('setBook', $ifrs->id)
        ->assertDispatched('accounting-context-changed');
});

test('ar and ap dashboard data is isolated to the selected book', function () {
    $this->seed(DemoDataSeeder::class);

    $company = Company::firstOrFail();
    $ifrs = AccountingBook::query()
        ->where('company_id', $company->id)
        ->where('code', 'IFRS')
        ->firstOrFail();

    $ar = app(ArApplicationService::class);
    $ap = app(ApApplicationService::class);

    expect($ar->aging($company, book: $ifrs)['total'])->toBe('0')
        ->and($ap->aging($company, book: $ifrs)['total'])->toBe('0')
        ->and($ar->reconcile($company, $ifrs)['passed'])->toBeTrue()
        ->and($ap->reconcile($company, $ifrs)['passed'])->toBeTrue();
});

/** Create a draft sales invoice in the seeded company's LOCAL book. */
function makeDraftInvoice(): SalesInvoice
{
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-SHOW-1', 'name_ar' => 'عميل عرض']);

    return app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 1000, 'tax_account' => '210404', 'tax' => 50],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-04-09']);
}

test('ar customer index renders', function () {
    Livewire::test(CustomerIndex::class)
        ->assertOk()
        ->assertSee(__('erp.customer.title'));
});

test('ar customer can be created via livewire', function () {
    Livewire::test(CustomerCreate::class)
        ->set('code', 'C-LIVE-1')
        ->set('name_ar', 'عميل اختبار')
        ->set('currency', 'LYD')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('ar.customers.show', 1));

    $this->assertDatabaseHas('customers', [
        'code' => 'C-LIVE-1',
        'name_ar' => 'عميل اختبار',
    ]);
});

test('ar customer duplicate company code is rejected as validation', function () {
    Livewire::test(CustomerCreate::class)
        ->set('code', 'C-DUPLICATE')
        ->set('name_ar', 'العميل الأول')
        ->set('currency', 'LYD')
        ->call('save')
        ->assertHasNoErrors();

    Livewire::test(CustomerCreate::class)
        ->set('code', 'C-DUPLICATE')
        ->set('name_ar', 'العميل الثاني')
        ->set('currency', 'USD')
        ->call('save')
        ->assertHasErrors(['code' => 'unique']);

    expect(Customer::query()->where('code', 'C-DUPLICATE')->count())->toBe(1);
});

test('sales invoice index renders', function () {
    Livewire::test(SalesInvoiceIndex::class)
        ->assertOk()
        ->assertSee(__('erp.sales_invoice.title'));
});

test('sales invoice draft rejects a zero value line before the accounting service', function () {
    $company = Company::firstOrFail();
    $customer = app(CustomerService::class)->create($company, [
        'code' => 'C-ZERO-LINE',
        'name_ar' => 'عميل اختبار القيمة الصفرية',
    ]);

    Livewire::test(SalesInvoiceCreate::class)
        ->set('customer_id', $customer->id)
        ->set('invoice_date', '2026-03-10')
        ->set('lines.0.description', 'Zero-value line')
        ->set('lines.0.quantity', '2')
        ->set('lines.0.unit_price', '0')
        ->call('save')
        ->assertHasErrors(['lines.0.unit_price' => 'gt']);

    expect(SalesInvoice::query()->where('company_id', $company->id)->count())->toBe(0);
});

test('sales invoice form resolves an approved company tax code through the tax engine', function () {
    $company = Company::firstOrFail();
    $customer = app(CustomerService::class)->create($company, [
        'code' => 'C-TAX-LINE',
        'name_ar' => 'عميل ضريبة',
    ]);
    $taxCode = TaxCode::query()->create([
        'company_id' => $company->id,
        'code' => 'WEB-VAT',
        'name' => 'Website VAT',
        'kind' => 'output_vat',
        'gl_account_code' => '210404',
        'is_active' => true,
    ]);
    $taxCode->rates()->create([
        'rate' => 5,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'legal_reference' => 'TEST-VAT',
        'status' => 'approved',
    ]);

    Livewire::test(SalesInvoiceCreate::class)
        ->set('customer_id', $customer->id)
        ->set('invoice_date', '2026-03-10')
        ->set('lines.0.description', 'Taxed line')
        ->set('lines.0.quantity', '1')
        ->set('lines.0.unit_price', '100')
        ->set('lines.0.tax_code', 'WEB-VAT')
        ->call('save')
        ->assertHasNoErrors();

    $invoice = SalesInvoice::query()->where('customer_id', $customer->id)->firstOrFail();
    expect($invoice->net_total)->toBe('100.000000')
        ->and($invoice->tax_total)->toBe('5.000000')
        ->and($invoice->gross_total)->toBe('105.000000')
        ->and($invoice->lines()->firstOrFail()->tax_account_code)->toBe('210404');
});

test('ar credit and debit note indexes render', function () {
    Livewire::test(CreditNoteIndex::class)->assertOk()->assertSee(__('erp.credit_note.ar_title'));
    Livewire::test(DebitNoteIndex::class)->assertOk()->assertSee(__('erp.debit_note.ar_title'));
});

test('ap credit and debit note indexes render', function () {
    Livewire::test(App\Livewire\Ap\CreditNoteIndex::class)->assertOk()->assertSee(__('erp.credit_note.ap_title'));
    Livewire::test(App\Livewire\Ap\DebitNoteIndex::class)->assertOk()->assertSee(__('erp.debit_note.ap_title'));
});

test('gl account balances and general ledger render', function () {
    Livewire::test(AccountBalances::class)->assertOk()->assertSee(__('erp.nav.account_balances'));
    Livewire::test(GeneralLedger::class)->assertOk()->assertSee(__('erp.nav.gl_ledger'));
});

test('fixed assets pages render', function () {
    Livewire::test(FixedAssetIndex::class)->assertOk()->assertSee(__('erp.assets.title'));
    Livewire::test(DepreciationRegister::class)->assertOk()->assertSee(__('erp.assets.depreciation'));
});

test('banking pages render', function () {
    Livewire::test(TreasuryAccounts::class, ['type' => 'bank'])->assertOk()->assertSee(__('erp.banking.bank_accounts'));
    Livewire::test(TreasuryAccounts::class, ['type' => 'cash'])->assertOk()->assertSee(__('erp.banking.cash_accounts'));
    Livewire::test(TransactionIndex::class)->assertOk()->assertSee(__('erp.banking.transactions'));
    Livewire::test(ReconciliationIndex::class)->assertOk()->assertSee(__('erp.banking.reconciliation'));
});

test('ar and ap statement and open items render', function () {
    Livewire::test(Statement::class)->assertOk()->assertSee(__('erp.statement_page.ar_title'));
    Livewire::test(OpenItems::class)->assertOk()->assertSee(__('erp.open_items.ar_title'));
    Livewire::test(App\Livewire\Ap\Statement::class)->assertOk()->assertSee(__('erp.statement_page.ap_title'));
    Livewire::test(App\Livewire\Ap\OpenItems::class)->assertOk()->assertSee(__('erp.open_items.ap_title'));
});

test('operations index pages render', function () {
    Livewire::test(Index::class)->assertOk()->assertSee(__('erp.expense.title'));
    Livewire::test(App\Livewire\Projects\Index::class)->assertOk()->assertSee(__('erp.project.title'));
    Livewire::test(App\Livewire\Investments\Index::class)->assertOk()->assertSee(__('erp.investment.title'));
    Livewire::test(App\Livewire\Tax\Index::class)->assertOk()->assertSee(__('erp.tax.codes_title'));
    Livewire::test(RuleIndex::class)->assertOk()->assertSee(__('erp.tax.rules_title'));
    Livewire::test(App\Livewire\OpeningBalances\Index::class)->assertOk()->assertSee(__('erp.opening.title'));
});

test('new reports render with book basis', function () {
    Livewire::test(Oci::class)->assertOk()->assertSee(__('erp.reports.oci'));
    Livewire::test(CashForecast::class)->assertOk()->assertSee(__('erp.reports.cash_forecast'));
    Livewire::test(BudgetVsActual::class)->assertOk()->assertSee(__('erp.reports.budget_vs_actual'));
});

test('cash flow lists real movements per cash account once entries are posted', function () {
    $this->seed(DemoDataSeeder::class);

    Livewire::test(CashFlow::class)
        ->assertOk()
        ->assertSee(__('erp.reports.cash_by_activity'))
        ->assertSee(__('erp.reports.cash_movements'))
        ->assertSee(__('erp.reports.activity_operating'))
        ->assertDontSee('journal_id');
});

test('cash flow and management pack render as readable statements, not raw data', function () {
    Livewire::test(CashFlow::class)
        ->assertOk()
        ->assertSee(__('erp.nav.cash_flow'))
        ->assertSee(__('erp.reports.cash_flow_hint'))
        ->assertDontSee('journal_id')
        ->assertDontSee('classification');

    Livewire::test(ManagementPack::class)
        ->assertOk()
        ->assertSee(__('erp.nav.management_pack'))
        ->assertSee(__('erp.reports.net_cash_change'))
        ->assertDontSee('book_basis');
});

test('subledger reconciliation pages state the result in plain language', function () {
    Livewire::test(Reconciliation::class)
        ->assertOk()
        ->assertSee(__('erp.reconciliation.gl_balance_ar'))
        ->assertSee(__('erp.reconciliation.subledger_ar'))
        ->assertDontSee('INV-7');

    Livewire::test(App\Livewire\Ap\Reconciliation::class)
        ->assertOk()
        ->assertSee(__('erp.reconciliation.gl_balance_ap'))
        ->assertSee(__('erp.reconciliation.subledger_ap'));
});

test('gl accounts index renders', function () {
    Livewire::test(AccountIndex::class)
        ->assertOk()
        ->assertSee(__('erp.nav.accounts'));
});

test('balance sheet report renders with book basis', function () {
    Livewire::test(BalanceSheet::class)
        ->assertOk()
        ->assertSee(__('erp.nav.balance_sheet'));
});

test('sales invoice workspace renders a draft with its lines and open balance', function () {
    $invoice = makeDraftInvoice();

    Livewire::test(SalesInvoiceShow::class, ['invoice' => $invoice])
        ->assertOk()
        ->assertSee(__('erp.document.lines'))
        ->assertSee(__('erp.document.open_balance'))
        ->assertSee(__('erp.action.post'))
        ->assertDontSee(__('erp.document.immutable_notice'));
});

test('posting a draft invoice via the workspace creates a journal and marks it posted', function () {
    $invoice = makeDraftInvoice();

    Livewire::test(SalesInvoiceShow::class, ['invoice' => $invoice])
        ->call('post')
        ->assertRedirect(route('ar.invoices.show', $invoice->id));

    $invoice->refresh();
    expect($invoice->status)->toBe(DocumentStatus::POSTED)
        ->and($invoice->journal_id)->not->toBeNull()
        ->and($invoice->number)->not->toBeNull();
});

test('invoice workspace presents a posting error when no open period contains the date', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, [
        'code' => 'C-POST-ERROR',
        'name_ar' => 'عميل خطأ الترحيل',
    ]);
    $invoice = app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2025-12-31']);

    Livewire::test(SalesInvoiceShow::class, ['invoice' => $invoice])
        ->call('post')
        ->assertHasErrors('posting')
        ->assertSee('no OPEN fiscal period');

    expect($invoice->fresh()->status)->toBe(DocumentStatus::DRAFT)
        ->and($invoice->fresh()->journal_id)->toBeNull();
});

test('receipt workspace renders its allocations panel and posts', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-RCP-1', 'name_ar' => 'عميل سند']);
    $receipt = app(ReceiptService::class)->createDraft($company, $book, $customer, 500, ['receipt_date' => '2026-03-10']);

    Livewire::test(ReceiptShow::class, ['receipt' => $receipt])
        ->assertOk()
        ->assertSee(__('erp.receipt.allocations'))
        ->call('post')
        ->assertRedirect(route('ar.receipts.show', $receipt->id));

    expect($receipt->fresh()->status)->toBe(DocumentStatus::POSTED)
        ->and($receipt->fresh()->journal_id)->not->toBeNull();
});

test('receipt can be created posted and allocated through livewire', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-RCP-FLOW', 'name_ar' => 'عميل دورة القبض']);
    $invoice = app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 1000],
    ], ['invoice_date' => '2026-03-10']);
    app(SalesInvoiceService::class)->post($invoice);

    Livewire::test(ReceiptCreate::class)
        ->set('customer_id', $customer->id)
        ->set('receipt_date', '2026-03-11')
        ->set('amount', '400')
        ->set('currency', 'LYD')
        ->set('cash_bank_account', '110102')
        ->call('save')
        ->assertHasNoErrors();

    $receipt = Receipt::query()->where('customer_id', $customer->id)->firstOrFail();
    Livewire::test(ReceiptShow::class, ['receipt' => $receipt])
        ->call('post')
        ->assertRedirect(route('ar.receipts.show', $receipt->id));

    Livewire::test(ReceiptShow::class, ['receipt' => $receipt->fresh()])
        ->set('invoice_id', $invoice->id)
        ->set('allocation_amount', '400')
        ->call('allocate')
        ->assertHasNoErrors();

    expect($receipt->fresh()->unallocated_amount)->toBe('0.000000')
        ->and($invoice->fresh()->openBalance())->toBe('600.000000')
        ->and($receipt->fresh()->journal_id)->not->toBeNull();
});

test('receipt allocation rejects an amount above the invoice balance', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-RCP-OVER', 'name_ar' => 'عميل تجاوز التخصيص']);
    $invoice = app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']);
    app(SalesInvoiceService::class)->post($invoice);
    $receipt = app(ReceiptService::class)->createDraft($company, $book, $customer, 200, ['receipt_date' => '2026-03-11']);
    app(ReceiptService::class)->post($receipt);

    Livewire::test(ReceiptShow::class, ['receipt' => $receipt->fresh()])
        ->set('invoice_id', $invoice->id)
        ->set('allocation_amount', '150')
        ->call('allocate')
        ->assertHasErrors('allocation');

    expect($invoice->fresh()->openBalance())->toBe('100.000000')
        ->and($receipt->fresh()->unallocated_amount)->toBe('200.000000');
});

test('ar credit note workspace renders with its lines', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-CN-1', 'name_ar' => 'عميل إشعار']);
    $note = app(CreditNoteService::class)->createDraft($company, $book, $customer, [['revenue_account' => '410101', 'net' => 200, 'tax' => 0]], ['credit_note_date' => '2026-03-11']);

    Livewire::test(CreditNoteShow::class, ['note' => $note])
        ->assertOk()
        ->assertSee(__('erp.credit_note.doc_title'))
        ->assertSee(__('erp.document.lines'));
});

test('customer credit and debit notes can be created and posted through livewire', function () {
    $company = Company::firstOrFail();
    $book = AccountingBook::where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $customer = app(CustomerService::class)->create($company, ['code' => 'C-NOTE-FLOW', 'name_ar' => 'عميل دورة الإشعارات']);
    $invoice = app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 500],
    ], ['invoice_date' => '2026-03-10']);
    app(SalesInvoiceService::class)->post($invoice);

    Livewire::test(NoteCreate::class, ['kind' => 'credit'])
        ->set('customer_id', $customer->id)
        ->set('document_date', '2026-03-12')
        ->set('lines.0.description', 'Customer allowance')
        ->set('lines.0.quantity', '1')
        ->set('lines.0.unit_price', '100')
        ->call('save')
        ->assertHasNoErrors();

    $credit = SalesCreditNote::query()->where('customer_id', $customer->id)->firstOrFail();
    Livewire::test(CreditNoteShow::class, ['note' => $credit])
        ->call('post')
        ->assertRedirect(route('ar.credit-notes.show', $credit->id));
    Livewire::test(CreditNoteShow::class, ['note' => $credit->fresh()])
        ->set('invoice_id', $invoice->id)
        ->set('allocation_amount', '100')
        ->call('allocate')
        ->assertHasNoErrors();

    Livewire::test(NoteCreate::class, ['kind' => 'debit'])
        ->set('customer_id', $customer->id)
        ->set('document_date', '2026-03-13')
        ->set('lines.0.description', 'Additional service')
        ->set('lines.0.quantity', '1')
        ->set('lines.0.unit_price', '50')
        ->call('save')
        ->assertHasNoErrors();

    $debit = SalesDebitNote::query()->where('customer_id', $customer->id)->firstOrFail();
    Livewire::test(DebitNoteShow::class, ['note' => $debit])
        ->call('post')
        ->assertRedirect(route('ar.debit-notes.show', $debit->id));

    expect($credit->fresh()->journal_id)->not->toBeNull()
        ->and($credit->fresh()->allocated_total)->toBe('100.000000')
        ->and($invoice->fresh()->openBalance())->toBe('400.000000')
        ->and($debit->fresh()->journal_id)->not->toBeNull()
        ->and($debit->fresh()->status)->toBe(DocumentStatus::POSTED);
});

test('posted invoice workspace shows the immutability notice and hides the post action', function () {
    $invoice = makeDraftInvoice();
    app(SalesInvoiceService::class)->post($invoice);

    Livewire::test(SalesInvoiceShow::class, ['invoice' => $invoice->fresh()])
        ->assertOk()
        ->assertSee(__('erp.document.immutable_notice'))
        ->assertSee(__('erp.document.view_journal'))
        ->assertDontSee(__('erp.action.confirm_post'));
});
