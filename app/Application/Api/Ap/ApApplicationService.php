<?php

declare(strict_types=1);

namespace App\Application\Api\Ap;

use App\Application\Api\ApiQuery;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseDebitNote;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use App\Models\Ap\SupplierPayment;
use App\Services\Ap\ApLedgerService;
use App\Services\Ap\ApReconciliationService;
use App\Services\Ap\PurchaseCreditNoteService;
use App\Services\Ap\PurchaseDebitNoteService;
use App\Services\Ap\PurchaseInvoiceService;
use App\Services\Ap\SupplierPaymentService;
use App\Services\Ap\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class ApApplicationService
{
    public function __construct(
        private readonly SupplierService $suppliers,
        private readonly PurchaseInvoiceService $invoices,
        private readonly SupplierPaymentService $payments,
        private readonly PurchaseCreditNoteService $creditNotes,
        private readonly PurchaseDebitNoteService $debitNotes,
        private readonly ApLedgerService $ledger,
        private readonly ApReconciliationService $reconciliation,
    ) {}

    /** @return LengthAwarePaginator<int, Supplier> */
    public function listSuppliers(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Supplier::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'legal_name', 'currency', 'created_at'],
            ['code' => 'code', 'currency' => 'currency', 'is_active' => 'is_active'],
            'code',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createSupplier(Company $company, array $attributes, ?int $actorId = null): Supplier
    {
        return $this->suppliers->create($company, $attributes, $actorId);
    }

    public function findSupplier(Company $company, int $id): Supplier
    {
        return Supplier::query()->where('company_id', $company->id)->where('id', $id)->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, PurchaseInvoice> */
    public function listInvoices(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            PurchaseInvoice::query()->where('company_id', $company->id)->where('book_id', $book->id)->with(['supplier', 'lines']),
            $request,
            ['id', 'number', 'invoice_date', 'status', 'created_at'],
            ['status' => 'status', 'supplier_id' => 'supplier_id', 'currency' => 'currency'],
            'invoice_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createInvoiceDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseInvoice
    {
        return $this->invoices->createDraft($company, $book, $supplier, $lines, $header);
    }

    public function postInvoice(PurchaseInvoice $invoice, ?int $posterId = null): PurchaseInvoice
    {
        return $this->invoices->post($invoice, $posterId);
    }

    public function findInvoice(Company $company, AccountingBook $book, int $id): PurchaseInvoice
    {
        return PurchaseInvoice::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, SupplierPayment> */
    public function listPayments(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            SupplierPayment::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('supplier'),
            $request,
            ['id', 'number', 'payment_date', 'status', 'created_at'],
            ['status' => 'status', 'supplier_id' => 'supplier_id'],
            'payment_date',
        );
    }

    /**
     * @param  array<string, mixed>  $header
     */
    public function createPaymentDraft(Company $company, AccountingBook $book, Supplier $supplier, string|float|int $amount, array $header = []): SupplierPayment
    {
        return $this->payments->createDraft($company, $book, $supplier, $amount, $header);
    }

    public function postPayment(SupplierPayment $payment, ?int $posterId = null): SupplierPayment
    {
        return $this->payments->post($payment, $posterId);
    }

    public function findPayment(Company $company, AccountingBook $book, int $id): SupplierPayment
    {
        return SupplierPayment::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function allocatePayment(SupplierPayment $payment, PurchaseInvoice $invoice, string|float|int $amount): mixed
    {
        return $this->payments->allocate($payment, $invoice, $amount);
    }

    /** @return LengthAwarePaginator<int, PurchaseCreditNote> */
    public function listCreditNotes(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            PurchaseCreditNote::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('supplier'),
            $request,
            ['id', 'number', 'credit_note_date', 'status', 'created_at'],
            ['status' => 'status', 'supplier_id' => 'supplier_id'],
            'credit_note_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createCreditNoteDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseCreditNote
    {
        return $this->creditNotes->createDraft($company, $book, $supplier, $lines, $header);
    }

    public function postCreditNote(PurchaseCreditNote $note, ?int $posterId = null): PurchaseCreditNote
    {
        return $this->creditNotes->post($note, $posterId);
    }

    public function allocateCreditNote(PurchaseCreditNote $note, PurchaseInvoice $invoice, string|float|int $amount): mixed
    {
        return $this->creditNotes->allocate($note, $invoice, $amount);
    }

    public function findCreditNote(Company $company, AccountingBook $book, int $id): PurchaseCreditNote
    {
        return PurchaseCreditNote::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, PurchaseDebitNote> */
    public function listDebitNotes(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            PurchaseDebitNote::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('supplier'),
            $request,
            ['id', 'number', 'debit_note_date', 'status', 'created_at'],
            ['status' => 'status', 'supplier_id' => 'supplier_id'],
            'debit_note_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDebitNoteDraft(Company $company, AccountingBook $book, Supplier $supplier, array $lines, array $header = []): PurchaseDebitNote
    {
        return $this->debitNotes->createDraft($company, $book, $supplier, $lines, $header);
    }

    public function postDebitNote(PurchaseDebitNote $note, ?int $posterId = null): PurchaseDebitNote
    {
        return $this->debitNotes->post($note, $posterId);
    }

    public function findDebitNote(Company $company, AccountingBook $book, int $id): PurchaseDebitNote
    {
        return PurchaseDebitNote::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return array<string, mixed> */
    public function supplierStatement(Supplier $supplier, ?Carbon $from = null, ?Carbon $to = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->statement($supplier, $from, $to, $book);
    }

    /** @return array<string, mixed> */
    public function aging(Company $company, ?Carbon $asOf = null, ?Supplier $supplier = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->aging($company, $asOf, $supplier, $book);
    }

    /** @return list<array<string, int|string|null>> */
    public function openItems(Supplier $supplier, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->openItems($supplier, $asOf, $book);
    }

    /** @return array<string, mixed> */
    public function reconcile(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $check = $this->reconciliation->reconcile($company, $book, asOf: $asOf);

        return [
            'code' => $check->code,
            'passed' => $check->passed(),
            'message' => $check->message,
            'expected' => $check->expected ?? null,
            'actual' => $check->actual ?? null,
        ];
    }
}
