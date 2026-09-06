<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Middleware\EnsureOnboarded;
use App\Livewire\Ap\Aging as ApAging;
use App\Livewire\Ap\CreditNoteIndex as ApCreditNoteIndex;
use App\Livewire\Ap\DebitNoteIndex as ApDebitNoteIndex;
use App\Livewire\Ap\NoteCreate as ApNoteCreate;
use App\Livewire\Ap\PaymentCreate;
use App\Livewire\Ap\PaymentShow;
use App\Livewire\Ap\PurchaseInvoiceCreate;
use App\Livewire\Ap\PurchaseInvoiceIndex;
use App\Livewire\Ap\PurchaseInvoiceShow;
use App\Livewire\Ap\Reconciliation as ApReconciliation;
use App\Livewire\Ap\SupplierCreate;
use App\Livewire\Ap\SupplierIndex;
use App\Livewire\Ap\SupplierPaymentIndex;
use App\Livewire\Ar\Aging as ArAging;
use App\Livewire\Ar\CreditNoteIndex as ArCreditNoteIndex;
use App\Livewire\Ar\CreditNoteShow;
use App\Livewire\Ar\CustomerCreate;
use App\Livewire\Ar\CustomerIndex;
use App\Livewire\Ar\CustomerShow;
use App\Livewire\Ar\DebitNoteIndex as ArDebitNoteIndex;
use App\Livewire\Ar\DebitNoteShow;
use App\Livewire\Ar\NoteCreate as ArNoteCreate;
use App\Livewire\Ar\OpenItems;
use App\Livewire\Ar\ReceiptCreate;
use App\Livewire\Ar\ReceiptIndex;
use App\Livewire\Ar\ReceiptShow;
use App\Livewire\Ar\Reconciliation as ArReconciliation;
use App\Livewire\Ar\SalesInvoiceCreate;
use App\Livewire\Ar\SalesInvoiceIndex;
use App\Livewire\Ar\SalesInvoiceShow;
use App\Livewire\Ar\Statement;
use App\Livewire\Assets\DepreciationRegister;
use App\Livewire\Assets\FixedAssetCreate;
use App\Livewire\Assets\FixedAssetIndex;
use App\Livewire\Assets\FixedAssetShow;
use App\Livewire\Banking\ReconciliationCreate;
use App\Livewire\Banking\ReconciliationIndex;
use App\Livewire\Banking\ReconciliationShow;
use App\Livewire\Banking\TransactionCreate;
use App\Livewire\Banking\TransactionIndex;
use App\Livewire\Banking\TransactionShow;
use App\Livewire\Banking\TreasuryAccountCreate;
use App\Livewire\Banking\TreasuryAccounts;
use App\Livewire\Banking\TreasuryAccountShow;
use App\Livewire\Books\Index as BooksIndex;
use App\Livewire\Dashboard\FinanceDashboard;
use App\Livewire\Expenses\ExpenseCreate;
use App\Livewire\Expenses\ExpenseShow;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\Gl\AccountBalances;
use App\Livewire\Gl\AccountIndex;
use App\Livewire\Gl\GeneralLedger;
use App\Livewire\Gl\JournalIndex;
use App\Livewire\Gl\JournalShow;
use App\Livewire\Gl\PeriodIndex;
use App\Livewire\Gl\TrialBalance;
use App\Livewire\Investments\Index as InvestmentsIndex;
use App\Livewire\Investments\InvestmentCreate;
use App\Livewire\Investments\InvestmentShow;
use App\Livewire\Onboarding\AccountingSetup;
use App\Livewire\Onboarding\CompanyDetails;
use App\Livewire\OpeningBalances\Index as OpeningBalancesIndex;
use App\Livewire\OpeningBalances\OpeningBalanceCreate;
use App\Livewire\OpeningBalances\OpeningBalanceShow;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\ProjectCreate;
use App\Livewire\Projects\ProjectShow;
use App\Livewire\Reports\BalanceSheet;
use App\Livewire\Reports\BudgetVsActual;
use App\Livewire\Reports\CashFlow;
use App\Livewire\Reports\CashForecast;
use App\Livewire\Reports\ManagementPack;
use App\Livewire\Reports\Oci;
use App\Livewire\Reports\ProfitLoss;
use App\Livewire\Tax\Index as TaxIndex;
use App\Livewire\Tax\RuleIndex;
use App\Livewire\Tax\TaxCodeCreate;
use App\Livewire\Tax\TaxRateCreate;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');

// First-time onboarding (authenticated + verified, but NOT yet requiring a company).
Route::middleware(['auth', 'verified'])->prefix('onboarding')->name('onboarding.')->group(function (): void {
    Route::get('/company', CompanyDetails::class)->name('company');
    Route::get('/accounting', AccountingSetup::class)->name('accounting');
});

Route::middleware(['auth', 'verified', EnsureOnboarded::class])->group(function (): void {
    Route::get('/dashboard', FinanceDashboard::class)->name('dashboard');

    Route::prefix('ar')->name('ar.')->group(function (): void {
        Route::get('/customers', CustomerIndex::class)->name('customers');
        Route::get('/customers/create', CustomerCreate::class)->name('customers.create');
        Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show');
        Route::get('/sales-invoices', SalesInvoiceIndex::class)->name('invoices');
        Route::get('/sales-invoices/create', SalesInvoiceCreate::class)->name('invoices.create');
        Route::get('/sales-invoices/{invoice}', SalesInvoiceShow::class)->name('invoices.show');
        Route::get('/receipts', ReceiptIndex::class)->name('receipts');
        Route::get('/receipts/create', ReceiptCreate::class)->name('receipts.create');
        Route::get('/receipts/{receipt}', ReceiptShow::class)->name('receipts.show');
        Route::get('/credit-notes', ArCreditNoteIndex::class)->name('credit-notes');
        Route::get('/credit-notes/create', ArNoteCreate::class)->defaults('kind', 'credit')->name('credit-notes.create');
        Route::get('/credit-notes/{note}', CreditNoteShow::class)->name('credit-notes.show');
        Route::get('/debit-notes', ArDebitNoteIndex::class)->name('debit-notes');
        Route::get('/debit-notes/create', ArNoteCreate::class)->defaults('kind', 'debit')->name('debit-notes.create');
        Route::get('/debit-notes/{note}', DebitNoteShow::class)->name('debit-notes.show');
        Route::get('/statement', Statement::class)->name('statement');
        Route::get('/open-items', OpenItems::class)->name('open-items');
        Route::get('/aging', ArAging::class)->name('aging');
        Route::get('/reconciliation', ArReconciliation::class)->name('reconciliation');
    });

    Route::prefix('ap')->name('ap.')->group(function (): void {
        Route::get('/suppliers', SupplierIndex::class)->name('suppliers');
        Route::get('/suppliers/create', SupplierCreate::class)->name('suppliers.create');
        Route::get('/purchase-invoices', PurchaseInvoiceIndex::class)->name('invoices');
        Route::get('/purchase-invoices/create', PurchaseInvoiceCreate::class)->name('invoices.create');
        Route::get('/purchase-invoices/{invoice}', PurchaseInvoiceShow::class)->name('invoices.show');
        Route::get('/supplier-payments', SupplierPaymentIndex::class)->name('payments');
        Route::get('/supplier-payments/create', PaymentCreate::class)->name('payments.create');
        Route::get('/supplier-payments/{payment}', PaymentShow::class)->name('payments.show');
        Route::get('/credit-notes', ApCreditNoteIndex::class)->name('credit-notes');
        Route::get('/credit-notes/create', ApNoteCreate::class)->defaults('kind', 'credit')->name('credit-notes.create');
        Route::get('/credit-notes/{note}', App\Livewire\Ap\CreditNoteShow::class)->name('credit-notes.show');
        Route::get('/debit-notes', ApDebitNoteIndex::class)->name('debit-notes');
        Route::get('/debit-notes/create', ApNoteCreate::class)->defaults('kind', 'debit')->name('debit-notes.create');
        Route::get('/debit-notes/{note}', App\Livewire\Ap\DebitNoteShow::class)->name('debit-notes.show');
        Route::get('/statement', App\Livewire\Ap\Statement::class)->name('statement');
        Route::get('/open-items', App\Livewire\Ap\OpenItems::class)->name('open-items');
        Route::get('/aging', ApAging::class)->name('aging');
        Route::get('/reconciliation', ApReconciliation::class)->name('reconciliation');
    });

    Route::prefix('gl')->name('gl.')->group(function (): void {
        Route::get('/accounts', AccountIndex::class)->name('accounts');
        Route::get('/journals', JournalIndex::class)->name('journals');
        Route::get('/journals/{journal}', JournalShow::class)->name('journals.show');
        Route::get('/ledger', GeneralLedger::class)->name('ledger');
        Route::get('/trial-balance', TrialBalance::class)->name('trial-balance');
        Route::get('/account-balances', AccountBalances::class)->name('account-balances');
        Route::get('/periods', PeriodIndex::class)->name('periods');
    });

    Route::prefix('assets')->name('assets.')->group(function (): void {
        Route::get('/', FixedAssetIndex::class)->name('index');
        Route::get('/create', FixedAssetCreate::class)->name('create');
        Route::get('/depreciation', DepreciationRegister::class)->name('depreciation');
        Route::get('/{asset}', FixedAssetShow::class)->name('show');
    });

    Route::prefix('banking')->name('banking.')->group(function (): void {
        Route::get('/', TreasuryAccounts::class)->defaults('type', 'bank')->name('index');
        Route::get('/bank-accounts', TreasuryAccounts::class)->defaults('type', 'bank')->name('bank-accounts');
        Route::get('/cash-accounts', TreasuryAccounts::class)->defaults('type', 'cash')->name('cash-accounts');
        Route::get('/bank-accounts/create', TreasuryAccountCreate::class)->defaults('type', 'bank')->name('bank-accounts.create');
        Route::get('/cash-accounts/create', TreasuryAccountCreate::class)->defaults('type', 'cash')->name('cash-accounts.create');
        Route::get('/accounts/{account}', TreasuryAccountShow::class)->name('accounts.show');
        Route::get('/transactions', TransactionIndex::class)->name('transactions');
        Route::get('/transactions/create', TransactionCreate::class)->name('transactions.create');
        Route::get('/transactions/{transaction}', TransactionShow::class)->name('transactions.show');
        Route::get('/reconciliation', ReconciliationIndex::class)->name('reconciliation');
        Route::get('/reconciliation/create', ReconciliationCreate::class)->name('reconciliation.create');
        Route::get('/reconciliation/{reconciliation}', ReconciliationShow::class)->name('reconciliation.show');
    });

    Route::get('/expenses', ExpensesIndex::class)->name('expenses.index');
    Route::get('/expenses/create', ExpenseCreate::class)->name('expenses.create');
    Route::get('/expenses/{claim}', ExpenseShow::class)->name('expenses.show');
    Route::get('/projects', ProjectsIndex::class)->name('projects.index');
    Route::get('/projects/create', ProjectCreate::class)->name('projects.create');
    Route::get('/projects/{project}', ProjectShow::class)->name('projects.show');
    Route::get('/investments', InvestmentsIndex::class)->name('investments.index');
    Route::get('/investments/create', InvestmentCreate::class)->name('investments.create');
    Route::get('/investments/{investment}', InvestmentShow::class)->name('investments.show');
    Route::get('/tax', TaxIndex::class)->name('tax.index');
    Route::get('/tax/create', TaxCodeCreate::class)->name('tax.create');
    Route::get('/tax/rules', RuleIndex::class)->name('tax.rules');
    Route::get('/tax/rules/create', TaxRateCreate::class)->name('tax.rules.create');
    Route::get('/opening-balances', OpeningBalancesIndex::class)->name('opening-balances.index');
    Route::get('/opening-balances/create', OpeningBalanceCreate::class)->name('opening-balances.create');
    Route::get('/opening-balances/{batch}', OpeningBalanceShow::class)->name('opening-balances.show');
    Route::get('/books', BooksIndex::class)->name('books.index');

    Route::prefix('reports')->name('reports.')->group(function (): void {
        Route::get('/balance-sheet', BalanceSheet::class)->name('balance-sheet');
        Route::get('/profit-loss', ProfitLoss::class)->name('profit-loss');
        Route::get('/cash-flow', CashFlow::class)->name('cash-flow');
        Route::get('/oci', Oci::class)->name('oci');
        Route::get('/cash-forecast', CashForecast::class)->name('cash-forecast');
        Route::get('/budget-vs-actual', BudgetVsActual::class)->name('budget-vs-actual');
        Route::get('/management-pack', ManagementPack::class)->name('management-pack');
    });
});

require __DIR__.'/settings.php';
