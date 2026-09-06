<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Ap\ApController;
use App\Http\Controllers\Api\V1\Ar\ArController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\DomainsController;
use App\Http\Controllers\Api\V1\Gl\GlController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Middleware\ResolveBookContext;
use App\Http\Middleware\ResolveCompanyContext;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/token', [AuthTokenController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::delete('auth/token', [AuthTokenController::class, 'destroy']);
        Route::get('me/context', [ContextController::class, 'me']);

        Route::middleware([ResolveCompanyContext::class, ResolveBookContext::class])->group(function (): void {
            // AA.2 AR
            Route::get('ar/customers', [ArController::class, 'customersIndex']);
            Route::post('ar/customers', [ArController::class, 'customersStore']);
            Route::get('ar/customers/{customer}', [ArController::class, 'customersShow']);
            Route::get('ar/customers/{customer}/statement', [ArController::class, 'customerStatement']);

            Route::get('ar/sales-invoices', [ArController::class, 'invoicesIndex']);
            Route::post('ar/sales-invoices', [ArController::class, 'invoicesStore']);
            Route::get('ar/sales-invoices/{invoice}', [ArController::class, 'invoicesShow']);
            Route::post('ar/sales-invoices/{invoice}/post', [ArController::class, 'invoicesPost']);

            Route::get('ar/receipts', [ArController::class, 'receiptsIndex']);
            Route::post('ar/receipts', [ArController::class, 'receiptsStore']);
            Route::post('ar/receipts/{receipt}/allocate', [ArController::class, 'receiptsAllocate']);

            Route::get('ar/credit-notes', [ArController::class, 'creditNotesIndex']);
            Route::post('ar/credit-notes', [ArController::class, 'creditNotesStore']);
            Route::get('ar/debit-notes', [ArController::class, 'debitNotesIndex']);
            Route::post('ar/debit-notes', [ArController::class, 'debitNotesStore']);

            Route::get('ar/aging', [ArController::class, 'aging']);
            Route::get('ar/open-items', [ArController::class, 'openItems']);
            Route::get('ar/reconciliation', [ArController::class, 'reconciliation']);

            // AA.3 AP
            Route::get('ap/suppliers', [ApController::class, 'suppliersIndex']);
            Route::post('ap/suppliers', [ApController::class, 'suppliersStore']);
            Route::get('ap/suppliers/{supplier}', [ApController::class, 'suppliersShow']);
            Route::get('ap/suppliers/{supplier}/statement', [ApController::class, 'supplierStatement']);

            Route::get('ap/purchase-invoices', [ApController::class, 'invoicesIndex']);
            Route::post('ap/purchase-invoices', [ApController::class, 'invoicesStore']);
            Route::post('ap/purchase-invoices/{invoice}/post', [ApController::class, 'invoicesPost']);

            Route::get('ap/supplier-payments', [ApController::class, 'paymentsIndex']);
            Route::post('ap/supplier-payments', [ApController::class, 'paymentsStore']);
            Route::post('ap/supplier-payments/{payment}/allocate', [ApController::class, 'paymentsAllocate']);

            Route::get('ap/supplier-credit-notes', [ApController::class, 'creditNotesIndex']);
            Route::post('ap/supplier-credit-notes', [ApController::class, 'creditNotesStore']);
            Route::get('ap/supplier-debit-notes', [ApController::class, 'debitNotesIndex']);
            Route::post('ap/supplier-debit-notes', [ApController::class, 'debitNotesStore']);

            Route::get('ap/aging', [ApController::class, 'aging']);
            Route::get('ap/open-items', [ApController::class, 'openItems']);
            Route::get('ap/reconciliation', [ApController::class, 'reconciliation']);

            // AA.4 GL
            Route::get('gl/accounts', [GlController::class, 'accountsIndex']);
            Route::get('gl/accounts/{account}', [GlController::class, 'accountsShow']);
            Route::get('gl/journals', [GlController::class, 'journalsIndex']);
            Route::post('gl/journals', [GlController::class, 'journalsStore']);
            Route::get('gl/journals/{journal}', [GlController::class, 'journalsShow']);
            Route::post('gl/journals/{journal}/post', [GlController::class, 'journalsPost']);
            Route::get('gl/journal-lines', [GlController::class, 'journalLinesIndex']);
            Route::get('gl/trial-balance', [GlController::class, 'trialBalance']);
            Route::get('gl/general-ledger', [GlController::class, 'generalLedger']);
            Route::get('gl/account-balances', [GlController::class, 'accountBalances']);
            Route::get('gl/periods', [GlController::class, 'periodsIndex']);

            // AA.5 Other domains
            Route::get('investments', [DomainsController::class, 'investmentsIndex']);
            Route::post('investments', [DomainsController::class, 'investmentsStore']);
            Route::get('investments/{investment}', [DomainsController::class, 'investmentsShow']);

            Route::get('expenses', [DomainsController::class, 'expensesIndex']);
            Route::post('expenses', [DomainsController::class, 'expensesStore']);
            Route::post('expenses/{expense}/submit', [DomainsController::class, 'expensesSubmit']);
            Route::post('expenses/{expense}/approve', [DomainsController::class, 'expensesApprove']);
            Route::post('expenses/{expense}/post', [DomainsController::class, 'expensesPost']);

            Route::get('projects', [DomainsController::class, 'projectsIndex']);
            Route::post('projects', [DomainsController::class, 'projectsStore']);
            Route::post('projects/{project}/charge', [DomainsController::class, 'projectsCharge']);
            Route::post('projects/{project}/capitalize', [DomainsController::class, 'projectsCapitalize']);

            Route::get('tax/codes', [DomainsController::class, 'taxCodesIndex']);
            Route::post('tax/codes', [DomainsController::class, 'taxCodesStore']);

            Route::get('opening-balances', [DomainsController::class, 'openingBalancesIndex']);
            Route::post('opening-balances', [DomainsController::class, 'openingBalancesStore']);

            Route::get('books', [DomainsController::class, 'booksIndex']);
            Route::get('books/reconcile', [DomainsController::class, 'booksReconcile']);

            Route::get('reports/balance-sheet', [ReportsController::class, 'balanceSheet']);
            Route::get('reports/profit-loss', [ReportsController::class, 'profitAndLoss']);
            Route::get('reports/cash-flow', [ReportsController::class, 'cashFlow']);
            Route::get('reports/management-pack', [ReportsController::class, 'managementPack']);
        });
    });
});
