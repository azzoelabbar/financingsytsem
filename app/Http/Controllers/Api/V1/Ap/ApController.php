<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Http\Controllers\Api\V1\ApiController;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ApController extends ApiController
{
    public function __construct(
        AccessControl $access,
        private readonly ApApplicationService $ap,
    ) {
        parent::__construct($access);
    }

    public function suppliersIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->paginated($this->ap->listSuppliers($this->company($request), $request));
    }

    public function suppliersStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'legal_name' => ['required', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        return $this->created($this->ap->createSupplier($this->company($request), $data, $this->actor($request)->id));
    }

    public function suppliersShow(Request $request, int $supplier): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->ok($this->ap->findSupplier($this->company($request), $supplier));
    }

    public function invoicesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->paginated($this->ap->listInvoices($this->company($request), $this->book($request), $request));
    }

    public function invoicesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'invoice_date' => ['required', 'date'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.expense_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $supplier = $this->ap->findSupplier($this->company($request), (int) $data['supplier_id']);
        $invoice = $this->ap->createInvoiceDraft(
            $this->company($request),
            $this->book($request),
            $supplier,
            $data['lines'],
            Arr::except($data, ['supplier_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $invoice = $this->ap->postInvoice($invoice, $this->actor($request)->id);
        }

        return $this->created($invoice->load('lines'));
    }

    public function invoicesPost(Request $request, int $invoice): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $doc = $this->ap->findInvoice($this->company($request), $this->book($request), $invoice);

        return $this->ok($this->ap->postInvoice($doc, $this->actor($request)->id)->load('lines'));
    }

    public function paymentsIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->paginated($this->ap->listPayments($this->company($request), $this->book($request), $request));
    }

    public function paymentsStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'payment_date' => ['required', 'date'],
            'number' => ['nullable', 'string', 'max:50'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $supplier = $this->ap->findSupplier($this->company($request), (int) $data['supplier_id']);
        $payment = $this->ap->createPaymentDraft(
            $this->company($request),
            $this->book($request),
            $supplier,
            $data['amount'],
            Arr::except($data, ['supplier_id', 'amount', 'post']),
        );
        if ($request->boolean('post')) {
            $payment = $this->ap->postPayment($payment, $this->actor($request)->id);
        }

        return $this->created($payment);
    }

    public function paymentsAllocate(Request $request, int $payment): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
        ]);
        $pay = $this->ap->findPayment($this->company($request), $this->book($request), $payment);
        $invoice = $this->ap->findInvoice($this->company($request), $this->book($request), (int) $data['invoice_id']);

        return $this->ok($this->ap->allocatePayment($pay, $invoice, $data['amount']));
    }

    public function creditNotesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->paginated($this->ap->listCreditNotes($this->company($request), $this->book($request), $request));
    }

    public function creditNotesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'credit_note_date' => ['required', 'date'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.expense_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $supplier = $this->ap->findSupplier($this->company($request), (int) $data['supplier_id']);
        $note = $this->ap->createCreditNoteDraft(
            $this->company($request),
            $this->book($request),
            $supplier,
            $data['lines'],
            Arr::except($data, ['supplier_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $note = $this->ap->postCreditNote($note, $this->actor($request)->id);
        }

        return $this->created($note->load('lines'));
    }

    public function debitNotesIndex(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);

        return $this->paginated($this->ap->listDebitNotes($this->company($request), $this->book($request), $request));
    }

    public function debitNotesStore(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_WRITE);
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'debit_note_date' => ['required', 'date'],
            'number' => ['nullable', 'string', 'max:50'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.expense_account' => ['required', 'string'],
            'lines.*.net' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'post' => ['sometimes', 'boolean'],
        ]);
        $supplier = $this->ap->findSupplier($this->company($request), (int) $data['supplier_id']);
        $note = $this->ap->createDebitNoteDraft(
            $this->company($request),
            $this->book($request),
            $supplier,
            $data['lines'],
            Arr::except($data, ['supplier_id', 'lines', 'post']),
        );
        if ($request->boolean('post')) {
            $note = $this->ap->postDebitNote($note, $this->actor($request)->id);
        }

        return $this->created($note->load('lines'));
    }

    public function supplierStatement(Request $request, int $supplier): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);
        $from = $request->filled('from') ? Carbon::parse((string) $request->input('from')) : null;
        $to = $request->filled('to') ? Carbon::parse((string) $request->input('to')) : null;

        return $this->ok($this->ap->supplierStatement(
            $this->ap->findSupplier($this->company($request), $supplier),
            $from,
            $to,
        ));
    }

    public function aging(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;
        $supplier = $request->filled('supplier_id')
            ? $this->ap->findSupplier($this->company($request), (int) $request->input('supplier_id'))
            : null;

        return $this->ok($this->ap->aging($this->company($request), $asOf, $supplier));
    }

    public function openItems(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);
        $data = $request->validate(['supplier_id' => ['required', 'integer']]);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->ap->openItems(
            $this->ap->findSupplier($this->company($request), (int) $data['supplier_id']),
            $asOf,
        ));
    }

    public function reconciliation(Request $request): JsonResponse
    {
        $this->authorizePermission($request, ApiPermission::AP_READ);
        $asOf = $request->filled('as_of') ? Carbon::parse((string) $request->input('as_of')) : null;

        return $this->ok($this->ap->reconcile($this->company($request), $this->book($request), $asOf));
    }
}
