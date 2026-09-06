<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class NoteCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $kind = 'credit';

    public ?int $supplier_id = null;

    public ?int $purchase_invoice_id = null;

    public string $document_date = '';

    public string $reason = '';

    public string $reference = '';

    /** @var array<int, array{description: string, expense_account: string, quantity: string, unit_price: string}> */
    public array $lines = [];

    public function mount(string $kind = 'credit'): void
    {
        abort_unless(in_array($kind, ['credit', 'debit'], true), 404);
        $this->kind = $kind;
        $this->document_date = now()->toDateString();
        $this->lines = [['description' => '', 'expense_account' => '510101', 'quantity' => '1', 'unit_price' => '0']];
    }

    public function updatedSupplierId(): void
    {
        $this->purchase_invoice_id = null;
    }

    public function addLine(): void
    {
        $this->lines[] = ['description' => '', 'expense_account' => '510101', 'quantity' => '1', 'unit_price' => '0'];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate([
            'supplier_id' => 'required|integer', 'purchase_invoice_id' => 'nullable|integer', 'document_date' => 'required|date',
            'reason' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:255', 'lines' => 'required|array|min:1',
            'lines.*.description' => 'nullable|string|max:255', 'lines.*.expense_account' => 'required|string',
            'lines.*.quantity' => 'required|numeric|gt:0', 'lines.*.unit_price' => 'required|numeric|gt:0',
        ]);
        $supplier = $ap->findSupplier($company, (int) $this->supplier_id);
        $invoice = $this->purchase_invoice_id ? $ap->findInvoice($company, $book, $this->purchase_invoice_id) : null;
        if ($invoice && $invoice->supplier_id !== $supplier->id) {
            $this->addError('purchase_invoice_id', __('erp.note.invoice_supplier_mismatch'));

            return;
        }
        $lines = array_values(array_map(static fn (array $line): array => [
            'expense_account' => $line['expense_account'], 'description' => $line['description'] ?: null,
            'quantity' => $line['quantity'], 'unit_price' => $line['unit_price'],
        ], $this->lines));
        $header = [$this->kind.'_note_date' => $this->document_date, 'purchase_invoice_id' => $invoice?->id,
            'reason' => $this->reason ?: null, 'reference' => $this->reference ?: null, 'created_by' => auth()->id()];
        $note = $this->kind === 'credit'
            ? $ap->createCreditNoteDraft($company, $book, $supplier, $lines, $header)
            : $ap->createDebitNoteDraft($company, $book, $supplier, $lines, $header);
        session()->flash('success', __('erp.note.created_success'));
        $this->redirectRoute($this->kind === 'credit' ? 'ap.credit-notes.show' : 'ap.debit-notes.show', $note->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $suppliers = $company ? Supplier::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get() : collect();
        $invoices = $company && $book && $this->supplier_id ? PurchaseInvoice::query()->where('company_id', $company->id)->where('book_id', $book->id)
            ->where('supplier_id', $this->supplier_id)->whereNotNull('number')->orderByDesc('invoice_date')->get() : collect();

        return view('livewire.ap.note-create', compact('suppliers', 'invoices'));
    }
}
