<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use App\Models\Ar\SalesInvoice;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class NoteCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $kind = 'credit';

    public ?int $customer_id = null;

    public ?int $sales_invoice_id = null;

    public string $document_date = '';

    public string $reason = '';

    public string $reference = '';

    /** @var array<int, array{description: string, revenue_account: string, quantity: string, unit_price: string}> */
    public array $lines = [];

    public function mount(string $kind = 'credit'): void
    {
        abort_unless(in_array($kind, ['credit', 'debit'], true), 404);
        $this->kind = $kind;
        $this->document_date = now()->toDateString();
        $this->lines = [
            ['description' => '', 'revenue_account' => '410101', 'quantity' => '1', 'unit_price' => '0'],
        ];
    }

    public function updatedCustomerId(): void
    {
        $this->sales_invoice_id = null;
    }

    public function addLine(): void
    {
        $this->lines[] = ['description' => '', 'revenue_account' => '410101', 'quantity' => '1', 'unit_price' => '0'];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();

        $this->validate([
            'customer_id' => 'required|integer',
            'sales_invoice_id' => 'nullable|integer',
            'document_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.revenue_account' => 'required|string',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_price' => 'required|numeric|gt:0',
        ]);

        $customer = $ar->findCustomer($company, (int) $this->customer_id);
        $invoice = $this->sales_invoice_id !== null
            ? $ar->findInvoice($company, $book, $this->sales_invoice_id)
            : null;
        if ($invoice !== null && $invoice->customer_id !== $customer->id) {
            $this->addError('sales_invoice_id', __('erp.note.invoice_customer_mismatch'));

            return;
        }

        $payload = array_values(array_map(static fn (array $line): array => [
            'revenue_account' => $line['revenue_account'],
            'description' => $line['description'] !== '' ? $line['description'] : null,
            'quantity' => $line['quantity'],
            'unit_price' => $line['unit_price'],
        ], $this->lines));
        $header = [
            $this->kind.'_note_date' => $this->document_date,
            'sales_invoice_id' => $invoice?->id,
            'reason' => $this->reason !== '' ? $this->reason : null,
            'reference' => $this->reference !== '' ? $this->reference : null,
            'created_by' => auth()->id(),
        ];

        $note = $this->kind === 'credit'
            ? $ar->createCreditNoteDraft($company, $book, $customer, $payload, $header)
            : $ar->createDebitNoteDraft($company, $book, $customer, $payload, $header);

        session()->flash('success', __('erp.note.created_success'));
        $this->redirectRoute($this->kind === 'credit' ? 'ar.credit-notes.show' : 'ar.debit-notes.show', $note->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $customers = $company
            ? Customer::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get()
            : collect();
        $invoices = $company && $book && $this->customer_id
            ? SalesInvoice::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->where('customer_id', $this->customer_id)
                ->whereNotNull('number')
                ->orderByDesc('invoice_date')
                ->get()
            : collect();

        return view('livewire.ar.note-create', compact('customers', 'invoices'));
    }
}
