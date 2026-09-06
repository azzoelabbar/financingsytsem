<?php

declare(strict_types=1);

namespace App\Application\Api\Ar;

use App\Application\Api\ApiQuery;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Ar\Customer;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesDebitNote;
use App\Models\Ar\SalesInvoice;
use App\Services\Ar\ArLedgerService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Ar\CreditNoteService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\DebitNoteService;
use App\Services\Ar\ReceiptService;
use App\Services\Ar\SalesInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Application layer for AR HTTP endpoints — no accounting logic here.
 */
final class ArApplicationService
{
    public function __construct(
        private readonly CustomerService $customers,
        private readonly SalesInvoiceService $invoices,
        private readonly ReceiptService $receipts,
        private readonly CreditNoteService $creditNotes,
        private readonly DebitNoteService $debitNotes,
        private readonly ArLedgerService $ledger,
        private readonly ArReconciliationService $reconciliation,
    ) {}

    /** @return LengthAwarePaginator<int, Customer> */
    public function listCustomers(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Customer::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'name_ar', 'currency', 'created_at'],
            ['code' => 'code', 'currency' => 'currency', 'is_active' => 'is_active'],
            'code',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCustomer(Company $company, array $attributes, ?int $actorId = null): Customer
    {
        return $this->customers->create($company, $attributes, $actorId);
    }

    public function findCustomer(Company $company, int $id): Customer
    {
        return Customer::query()->where('company_id', $company->id)->where('id', $id)->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, SalesInvoice> */
    public function listInvoices(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            SalesInvoice::query()->where('company_id', $company->id)->where('book_id', $book->id)->with(['customer', 'lines', 'writeoffs']),
            $request,
            ['id', 'number', 'invoice_date', 'status', 'created_at'],
            ['status' => 'status', 'customer_id' => 'customer_id', 'currency' => 'currency'],
            'invoice_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createInvoiceDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesInvoice
    {
        return $this->invoices->createDraft($company, $book, $customer, $lines, $header);
    }

    public function postInvoice(SalesInvoice $invoice, ?int $posterId = null): SalesInvoice
    {
        return $this->invoices->post($invoice, $posterId);
    }

    public function findInvoice(Company $company, AccountingBook $book, int $id): SalesInvoice
    {
        return SalesInvoice::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, Receipt> */
    public function listReceipts(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Receipt::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('customer'),
            $request,
            ['id', 'number', 'receipt_date', 'status', 'created_at'],
            ['status' => 'status', 'customer_id' => 'customer_id'],
            'receipt_date',
        );
    }

    /**
     * @param  array<string, mixed>  $header
     */
    public function createReceiptDraft(Company $company, AccountingBook $book, Customer $customer, string|float|int $amount, array $header = []): Receipt
    {
        return $this->receipts->createDraft($company, $book, $customer, $amount, $header);
    }

    public function postReceipt(Receipt $receipt, ?int $posterId = null): Receipt
    {
        return $this->receipts->post($receipt, $posterId);
    }

    public function findReceipt(Company $company, AccountingBook $book, int $id): Receipt
    {
        return Receipt::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function allocateReceipt(Receipt $receipt, SalesInvoice $invoice, string|float|int $amount): mixed
    {
        return $this->receipts->allocate($receipt, $invoice, $amount);
    }

    /** @return LengthAwarePaginator<int, SalesCreditNote> */
    public function listCreditNotes(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            SalesCreditNote::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('customer'),
            $request,
            ['id', 'number', 'credit_note_date', 'status', 'created_at'],
            ['status' => 'status', 'customer_id' => 'customer_id'],
            'credit_note_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createCreditNoteDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesCreditNote
    {
        return $this->creditNotes->createDraft($company, $book, $customer, $lines, $header);
    }

    public function postCreditNote(SalesCreditNote $note, ?int $posterId = null): SalesCreditNote
    {
        return $this->creditNotes->post($note, $posterId);
    }

    public function allocateCreditNote(SalesCreditNote $note, SalesInvoice $invoice, string|float|int $amount): mixed
    {
        return $this->creditNotes->allocate($note, $invoice, $amount);
    }

    public function findCreditNote(Company $company, AccountingBook $book, int $id): SalesCreditNote
    {
        return SalesCreditNote::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, SalesDebitNote> */
    public function listDebitNotes(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            SalesDebitNote::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('customer'),
            $request,
            ['id', 'number', 'debit_note_date', 'status', 'created_at'],
            ['status' => 'status', 'customer_id' => 'customer_id'],
            'debit_note_date',
        );
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     */
    public function createDebitNoteDraft(Company $company, AccountingBook $book, Customer $customer, array $lines, array $header = []): SalesDebitNote
    {
        return $this->debitNotes->createDraft($company, $book, $customer, $lines, $header);
    }

    public function postDebitNote(SalesDebitNote $note, ?int $posterId = null): SalesDebitNote
    {
        return $this->debitNotes->post($note, $posterId);
    }

    public function findDebitNote(Company $company, AccountingBook $book, int $id): SalesDebitNote
    {
        return SalesDebitNote::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    /** @return array<string, mixed> */
    public function customerStatement(Customer $customer, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->statement($customer, $asOf, $book);
    }

    /** @return array<string, mixed> */
    public function aging(Company $company, ?Carbon $asOf = null, ?Customer $customer = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->aging($company, $asOf, $customer, $book);
    }

    /** @return list<array<string, int|string|null>> */
    public function openItems(Customer $customer, ?Carbon $asOf = null, ?AccountingBook $book = null): array
    {
        return $this->ledger->openItems($customer, $asOf, $book);
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
