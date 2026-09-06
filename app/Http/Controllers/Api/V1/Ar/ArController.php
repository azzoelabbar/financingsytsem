<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Http\Controllers\Api\V1\ApiController;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ArController extends ApiController
{
    public function __construct(
        AccessControl $access,
        private readonly ArApplicationService $ar,
    ) {
        parent::__construct($access);
    }

    public function customersIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->paginated($this->ar->listCustomers($this->company($request), $request));
    }

    public function customersStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name_ar' => ['required', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        return $this->created($this->ar->createCustomer($this->company($request), $data, $this->actor($request)->id));
    }

    public function customersShow(Request $request, int $customer): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->ok($this->ar->findCustomer($this->company($request), $customer));
    }

    public function invoicesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->paginated($this->ar->listInvoices($this->company($request), $this->book($request), $request));
    }

    public function invoicesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'invoice_date' => ['required', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.revenue_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $customer = $this->ar->findCustomer($this->company($request), (int) $data['customer_id']);
        $invoice = $this->ar->createInvoiceDraft(
            $this->company($request),
            $this->book($request),
            $customer,
            $data['lines'],
            Arr::except($data, ['customer_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $invoice = $this->ar->postInvoice($invoice, $this->actor($request)->id);
        }

        return $this->created($invoice->load('lines'));
    }

    public function invoicesShow(Request $request, int $invoice): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->ok($this->ar->findInvoice($this->company($request), $this->book($request), $invoice)->load('lines'));
    }

    public function invoicesPost(Request $request, int $invoice): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $doc = $this->ar->findInvoice($this->company($request), $this->book($request), $invoice);

        return $this->ok($this->ar->postInvoice($doc, $this->actor($request)->id)->load('lines'));
    }

    public function receiptsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->paginated($this->ar->listReceipts($this->company($request), $this->book($request), $request));
    }

    public function receiptsStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'receipt_date' => ['required', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric'],
            'number' => ['nullable', 'string', 'max:50'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $customer = $this->ar->findCustomer($this->company($request), (int) $data['customer_id']);
        $receipt = $this->ar->createReceiptDraft(
            $this->company($request),
            $this->book($request),
            $customer,
            $data['amount'],
            Arr::except($data, ['customer_id', 'amount', 'post']),
        );
        if ($request->boolean('post')) {
            $receipt = $this->ar->postReceipt($receipt, $this->actor($request)->id);
        }

        return $this->created($receipt);
    }

    public function receiptsAllocate(Request $request, int $receipt): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
        ]);
        $rcp = $this->ar->findReceipt($this->company($request), $this->book($request), $receipt);
        $invoice = $this->ar->findInvoice($this->company($request), $this->book($request), (int) $data['invoice_id']);

        return $this->ok($this->ar->allocateReceipt($rcp, $invoice, $data['amount']));
    }

    public function creditNotesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->paginated($this->ar->listCreditNotes($this->company($request), $this->book($request), $request));
    }

    public function creditNotesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'credit_note_date' => ['required', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.revenue_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $customer = $this->ar->findCustomer($this->company($request), (int) $data['customer_id']);
        $note = $this->ar->createCreditNoteDraft(
            $this->company($request),
            $this->book($request),
            $customer,
            $data['lines'],
            Arr::except($data, ['customer_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $note = $this->ar->postCreditNote($note, $this->actor($request)->id);
        }

        return $this->created($note->load('lines'));
    }

    public function debitNotesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);

        return $this->paginated($this->ar->listDebitNotes($this->company($request), $this->book($request), $request));
    }

    public function debitNotesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_WRITE);
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'debit_note_date' => ['required', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.revenue_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $customer = $this->ar->findCustomer($this->company($request), (int) $data['customer_id']);
        $note = $this->ar->createDebitNoteDraft(
            $this->company($request),
            $this->book($request),
            $customer,
            $data['lines'],
            Arr::except($data, ['customer_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $note = $this->ar->postDebitNote($note, $this->actor($request)->id);
        }

        return $this->created($note->load('lines'));
    }

    public function customerStatement(Request $request, int $customer): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->ar->customerStatement(
            $this->ar->findCustomer($this->company($request), $customer),
            $asOf,
        ));
    }

    public function aging(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;
        $customer = $request->filled('customer_id')
            ? $this->ar->findCustomer($this->company($request), (int) $request->input('customer_id'))
            : null;

        return $this->ok($this->ar->aging($this->company($request), $asOf, $customer));
    }

    public function openItems(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);
        $data = $request->validate(['customer_id' => ['required', 'integer']]);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->ar->openItems(
            $this->ar->findCustomer($this->company($request), (int) $data['customer_id']),
            $asOf,
        ));
    }

    public function reconciliation(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AR_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->ar->reconcile($this->company($request), $this->book($request), $asOf));
    }
}
