<?php

declare(strict_types=1);

use App\Enums\Ap\DocumentStatus;
use App\Livewire\Ap\CreditNoteShow;
use App\Livewire\Ap\DebitNoteShow;
use App\Livewire\Ap\NoteCreate;
use App\Livewire\Ap\PaymentCreate;
use App\Livewire\Ap\PaymentShow;
use App\Livewire\Ap\PurchaseInvoiceCreate;
use App\Livewire\Ap\PurchaseInvoiceShow;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseDebitNote;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\SupplierPayment;
use App\Models\User;
use App\Services\Ap\PurchaseInvoiceService;
use App\Services\Ap\SupplierService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
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

function payableContext(string $code): array
{
    $company = Company::firstOrFail();
    $book = AccountingBook::query()->where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();
    $supplier = app(SupplierService::class)->create($company, [
        'code' => $code, 'legal_name' => 'Writable Supplier', 'name_ar' => 'مورد قابل للكتابة', 'currency' => 'LYD',
    ]);

    return [$company, $book, $supplier];
}

test('purchase invoice is created and posted from its workspace', function () {
    [$company, $book, $supplier] = payableContext('SUP-WRITE-1');
    Livewire::test(PurchaseInvoiceCreate::class)
        ->set('supplier_id', $supplier->id)->set('invoice_date', '2026-04-01')
        ->set('lines.0.description', 'Office services')->set('lines.0.quantity', '2')->set('lines.0.unit_price', '300')
        ->call('save')->assertHasNoErrors();
    $invoice = PurchaseInvoice::query()->where('supplier_id', $supplier->id)->firstOrFail();
    Livewire::test(PurchaseInvoiceShow::class, ['invoice' => $invoice])->call('post')->assertRedirect(route('ap.invoices.show', $invoice->id));
    expect($invoice->fresh()->status)->toBe(DocumentStatus::POSTED)->and($invoice->fresh()->journal_id)->not->toBeNull()->and($invoice->fresh()->gross_total)->toBe('600.000000');
});

test('supplier payment posts and partially settles a purchase invoice', function () {
    [$company, $book, $supplier] = payableContext('SUP-WRITE-2');
    $invoice = app(PurchaseInvoiceService::class)->createDraft($company, $book, $supplier, [['expense_account' => '510101', 'net' => 600]], ['invoice_date' => '2026-04-01']);
    app(PurchaseInvoiceService::class)->post($invoice);
    Livewire::test(PaymentCreate::class)->set('supplier_id', $supplier->id)->set('payment_date', '2026-04-02')->set('amount', '250')
        ->set('currency', 'LYD')->set('cash_bank_account', '110102')->call('save')->assertHasNoErrors();
    $payment = SupplierPayment::query()->where('supplier_id', $supplier->id)->firstOrFail();
    Livewire::test(PaymentShow::class, ['payment' => $payment])->call('post')->assertRedirect(route('ap.payments.show', $payment->id));
    Livewire::test(PaymentShow::class, ['payment' => $payment->fresh()])->set('invoice_id', $invoice->id)->set('allocation_amount', '250')->call('allocate')->assertHasNoErrors();
    expect($invoice->fresh()->openBalance())->toBe('350.000000')->and($payment->fresh()->unallocated_amount)->toBe('0.000000')->and($payment->fresh()->journal_id)->not->toBeNull();
});

test('supplier credit and debit notes are writable and post journals', function () {
    [$company, $book, $supplier] = payableContext('SUP-WRITE-3');
    $invoice = app(PurchaseInvoiceService::class)->createDraft($company, $book, $supplier, [['expense_account' => '510101', 'net' => 500]], ['invoice_date' => '2026-04-01']);
    app(PurchaseInvoiceService::class)->post($invoice);
    Livewire::test(NoteCreate::class, ['kind' => 'credit'])->set('supplier_id', $supplier->id)->set('purchase_invoice_id', $invoice->id)
        ->set('document_date', '2026-04-03')->set('lines.0.quantity', '1')->set('lines.0.unit_price', '100')->call('save')->assertHasNoErrors();
    $credit = PurchaseCreditNote::query()->where('supplier_id', $supplier->id)->firstOrFail();
    Livewire::test(CreditNoteShow::class, ['note' => $credit])->call('post')->assertRedirect(route('ap.credit-notes.show', $credit->id));
    Livewire::test(NoteCreate::class, ['kind' => 'debit'])->set('supplier_id', $supplier->id)->set('document_date', '2026-04-04')
        ->set('lines.0.quantity', '1')->set('lines.0.unit_price', '50')->call('save')->assertHasNoErrors();
    $debit = PurchaseDebitNote::query()->where('supplier_id', $supplier->id)->firstOrFail();
    Livewire::test(DebitNoteShow::class, ['note' => $debit])->call('post')->assertRedirect(route('ap.debit-notes.show', $debit->id));
    expect($credit->fresh()->journal_id)->not->toBeNull()->and($credit->fresh()->allocated_total)->toBe('100.000000')
        ->and($invoice->fresh()->openBalance())->toBe('400.000000')->and($debit->fresh()->journal_id)->not->toBeNull();
});
