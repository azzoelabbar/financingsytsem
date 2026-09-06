<?php

declare(strict_types=1);

use App\Enums\Ap\DocumentStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\AuditLog;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ap\ApLedgerService;
use App\Services\Ap\ApReconciliationService;
use App\Services\Ap\Exceptions\AllocationExceedsBalanceException;
use App\Services\Ap\Exceptions\CurrencyMismatchException;
use App\Services\Ap\Exceptions\DuplicateDocumentException;
use App\Services\Ap\Exceptions\InactiveSupplierException;
use App\Services\Ap\PurchaseCreditNoteService;
use App\Services\Ap\PurchaseDebitNoteService;
use App\Services\Ap\PurchaseInvoiceService;
use App\Services\Ap\SupplierPaymentService;
use App\Services\Ap\SupplierService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail()->id;
    $this->apControl = app(ControlAccountResolver::class)->apControlCode($this->company);

    $this->suppliers = app(SupplierService::class);
    $this->invoices = app(PurchaseInvoiceService::class);
    $this->payments = app(SupplierPaymentService::class);
    $this->creditNotes = app(PurchaseCreditNoteService::class);
    $this->debitNotes = app(PurchaseDebitNoteService::class);
    $this->ledger = app(ApLedgerService::class);
    $this->recon = app(ApReconciliationService::class);

    $this->supplier = $this->suppliers->create($this->company, [
        'code' => 'S-001',
        'legal_name' => 'مورد تجريبي',
        'tax_id' => 'TAX-001',
        'default_expense_account_code' => '620201',
        'currency' => 'LYD',
    ]);
});

function apline($journal, callable $acc, string $code)
{
    return $journal->lines->firstWhere('account_id', $acc($code));
}

it('creates a supplier with the chart-resolved AP control account', function () {
    expect($this->supplier->code)->toBe('S-001')
        ->and($this->supplier->ap_control_code)->toBe($this->apControl)
        ->and($this->apControl)->not->toBe('')
        ->and(Account::where('company_id', $this->company->id)->where('code', $this->apControl)->where('is_control', true)->exists())->toBeTrue();

    $this->suppliers->addContact($this->supplier, ['name' => 'علي', 'is_primary' => true]);
    $this->suppliers->addAddress($this->supplier, ['line1' => 'طرابلس', 'is_primary' => true]);
    $this->suppliers->addBankAccount($this->supplier, ['bank_name' => 'مصرف الوحدة', 'account_number' => '123']);
    $this->suppliers->addTaxProfile($this->supplier, ['tax_number' => 'TAX-001']);

    expect($this->supplier->contacts)->toHaveCount(1)
        ->and($this->supplier->addresses)->toHaveCount(1)
        ->and($this->supplier->bankAccounts)->toHaveCount(1)
        ->and($this->supplier->taxProfiles)->toHaveCount(1);
});

it('runs the full AP lifecycle keeping every invariant', function () {
    // Invoice 10,000: Dr Expense 9,000 / Dr Input VAT 1,000 / Cr AP 10,000
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 9000, 'tax_account' => '110210', 'tax' => 1000],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-04-09', 'supplier_invoice_number' => 'VEN-100']);
    $invoice = $this->invoices->post($invoice);

    expect($invoice->status)->toBe(DocumentStatus::POSTED)
        ->and($invoice->journal_id)->not->toBeNull();

    $ij = $invoice->journal()->with('lines')->first();
    expect($ij->isBalanced())->toBeTrue()
        ->and((float) apline($ij, $this->acc, '620201')->debit)->toBe(9000.0)
        ->and((float) apline($ij, $this->acc, '110210')->debit)->toBe(1000.0)
        ->and((float) apline($ij, $this->acc, $this->apControl)->credit)->toBe(10000.0);

    // Partial payment 4,000
    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 4000, [
        'payment_date' => '2026-03-20',
    ]));
    $pj = $payment->journal()->with('lines')->first();
    expect((float) apline($pj, $this->acc, $this->apControl)->debit)->toBe(4000.0);

    $this->payments->allocate($payment, $invoice, 4000);
    expect((float) $invoice->fresh()->openBalance())->toBe(6000.0);

    $aging = $this->ledger->aging($this->company);
    expect((float) $aging['total'])->toBe(6000.0);

    $check = $this->recon->assert($this->company, $this->book);
    expect($check->passed())->toBeTrue()
        ->and((float) $check->expected)->toBe(6000.0);

    // Credit note 1,000 auto-allocated to the invoice
    $cn = $this->creditNotes->post($this->creditNotes->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 1000],
    ], ['credit_note_date' => '2026-03-25', 'purchase_invoice_id' => $invoice->id]));

    $cnj = $cn->journal()->with('lines')->first();
    expect((float) apline($cnj, $this->acc, $this->apControl)->debit)->toBe(1000.0)
        ->and((float) apline($cnj, $this->acc, '620201')->credit)->toBe(1000.0)
        ->and((float) $invoice->fresh()->openBalance())->toBe(5000.0);

    $this->recon->assert($this->company, $this->book);
    expect((float) $this->ledger->subledgerTotal($this->company))->toBe(5000.0);

    $integrity = app(IntegrityService::class)->check($this->company, $this->book);
    expect($integrity['passed'])->toBeTrue();

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();

    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id));
    expect($bs['balanced'])->toBeTrue();

    expect(AuditLog::where('auditable_type', $invoice::class)->where('event', 'posted')->exists())->toBeTrue();
});

it('posts a multi-line tax invoice', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 3000, 'tax_account' => '110210', 'tax' => 150],
        ['expense_account' => '620201', 'net' => 2000, 'tax_account' => '110210', 'tax' => 100],
    ], ['invoice_date' => '2026-03-11', 'supplier_invoice_number' => 'VEN-ML']));

    $j = $invoice->journal()->with('lines')->first();
    expect($j->isBalanced())->toBeTrue()
        ->and((float) apline($j, $this->acc, $this->apControl)->credit)->toBe(5250.0)
        ->and((float) $invoice->gross_total)->toBe(5250.0);

    $this->recon->assert($this->company, $this->book);
});

it('fully pays an invoice and clears the open item', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 500],
    ], ['invoice_date' => '2026-03-10']));

    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 500, [
        'payment_date' => '2026-03-15',
    ]));
    $this->payments->allocate($payment, $invoice, 500);

    expect((float) $invoice->fresh()->openBalance())->toBe(0.0)
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(0.0);
    $this->recon->assert($this->company, $this->book);
});

it('allocates one payment across multiple invoices', function () {
    $a = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 300],
    ], ['invoice_date' => '2026-03-10', 'supplier_invoice_number' => 'A']));
    $b = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 200],
    ], ['invoice_date' => '2026-03-11', 'supplier_invoice_number' => 'B']));

    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 500, [
        'payment_date' => '2026-03-20',
    ]));
    $this->payments->allocateMany($payment, [
        ['invoice' => $a, 'amount' => 300],
        ['invoice' => $b, 'amount' => 200],
    ]);

    expect((float) $a->fresh()->openBalance())->toBe(0.0)
        ->and((float) $b->fresh()->openBalance())->toBe(0.0)
        ->and((float) $payment->fresh()->unallocated_amount)->toBe(0.0);
    $this->recon->assert($this->company, $this->book);
});

it('posts a debit note as Dr Expense / Cr AP', function () {
    $dn = $this->debitNotes->post($this->debitNotes->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 250, 'tax_account' => '110210', 'tax' => 25],
    ], ['debit_note_date' => '2026-03-12']));

    $j = $dn->journal()->with('lines')->first();
    expect((float) apline($j, $this->acc, '620201')->debit)->toBe(250.0)
        ->and((float) apline($j, $this->acc, $this->apControl)->credit)->toBe(275.0)
        ->and($dn->journal_id)->not->toBeNull();

    $this->recon->assert($this->company, $this->book);
});

it('reverses a payment, restoring invoice open balance and INV-8', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 800],
    ], ['invoice_date' => '2026-03-10']));
    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 300, [
        'payment_date' => '2026-03-18',
    ]));
    $this->payments->allocate($payment, $invoice, 300);

    $this->payments->reverse($payment, reason: 'خطأ في التحويل');

    expect($payment->fresh()->status)->toBe(DocumentStatus::REVERSED)
        ->and((float) $invoice->fresh()->openBalance())->toBe(800.0);

    $this->recon->assert($this->company, $this->book);
    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
});

it('reverses an unallocated invoice through a mirror journal', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 120],
    ], ['invoice_date' => '2026-03-10']));

    $this->invoices->reverse($invoice, reason: 'إلغاء');

    expect($invoice->fresh()->status)->toBe(DocumentStatus::REVERSED)
        ->and((float) $this->ledger->subledgerTotal($this->company))->toBe(0.0);
    $this->recon->assert($this->company, $this->book);
});

it('builds a supplier statement whose closing balance equals the AP control', function () {
    $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 400],
    ], ['invoice_date' => '2026-03-10', 'due_date' => '2026-04-09']));

    $statement = $this->ledger->statement($this->supplier);
    expect($statement['supplier']['code'])->toBe('S-001')
        ->and($statement['movements'])->not->toBeEmpty()
        ->and((float) $statement['closing_balance'])->toBe(400.0)
        ->and((float) $statement['aging']['total'])->toBe(400.0)
        ->and($statement['open_items'][0]['days_outstanding'])->toBeInt();

    $this->recon->assert($this->company, $this->book);
});

it('keeps an unapplied payment reconciling to the AP control', function () {
    $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 100],
    ], ['invoice_date' => '2026-03-10']));
    $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 250, [
        'payment_date' => '2026-03-20',
    ]));

    expect((float) $this->ledger->subledgerTotal($this->company))->toBe(-150.0);
    $this->recon->assert($this->company, $this->book);
});

it('keeps posted invoices immutable', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 50],
    ], ['invoice_date' => '2026-03-10']));
    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('rejects a duplicate supplier invoice number', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 50],
    ], ['invoice_date' => '2026-03-10', 'supplier_invoice_number' => 'DUP-1']);

    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 60],
    ], ['invoice_date' => '2026-03-11', 'supplier_invoice_number' => 'DUP-1']);
})->throws(DuplicateDocumentException::class);

it('rejects an allocation that exceeds the invoice open balance', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 80],
    ], ['invoice_date' => '2026-03-10']));
    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 200, [
        'payment_date' => '2026-03-20',
    ]));
    $this->payments->allocate($payment, $invoice, 100);
})->throws(AllocationExceedsBalanceException::class);

it('refuses to post into a hard-closed period', function () {
    FiscalPeriod::where('company_id', $this->company->id)->where('period_no', 3)->update(['status' => 'hard_closed']);
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 40],
    ], ['invoice_date' => '2026-03-10']);
    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('rejects an invalid expense account at posting', function () {
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '999999', 'net' => 40],
    ], ['invoice_date' => '2026-03-10']);
    $this->invoices->post($invoice);
})->throws(PostingException::class);

it('rejects a zero-amount invoice', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 0],
    ], ['invoice_date' => '2026-03-10']);
})->throws(PostingException::class);

it('rejects an invalid currency', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 10],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'LY']);
})->throws(PostingException::class);

it('rejects a non-positive exchange rate', function () {
    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 10],
    ], ['invoice_date' => '2026-03-10', 'exchange_rate' => 0]);
})->throws(PostingException::class);

it('rejects allocating a payment in a different currency', function () {
    $invoice = $this->invoices->post($this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 100],
    ], ['invoice_date' => '2026-03-10', 'currency' => 'LYD']));
    $payment = $this->payments->post($this->payments->createDraft($this->company, $this->book, $this->supplier, 100, [
        'payment_date' => '2026-03-20',
        'currency' => 'USD',
        'exchange_rate' => 1,
    ]));
    $this->payments->allocate($payment, $invoice, 100);
})->throws(CurrencyMismatchException::class);

it('rejects posting for an inactive supplier', function () {
    $this->suppliers->deactivate($this->supplier);
    $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 10],
    ], ['invoice_date' => '2026-03-10']);
})->throws(InactiveSupplierException::class);

it('rejects posting when a mandatory dimension is missing', function () {
    Account::where('company_id', $this->company->id)->where('code', '620201')->update(['requires_cost_center' => true]);
    $invoice = $this->invoices->createDraft($this->company, $this->book, $this->supplier, [
        ['expense_account' => '620201', 'net' => 40],
    ], ['invoice_date' => '2026-03-10']);
    $this->invoices->post($invoice);
})->throws(PostingException::class);
