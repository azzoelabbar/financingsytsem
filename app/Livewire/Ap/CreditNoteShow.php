<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseCreditNote;
use App\Models\Ap\PurchaseInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CreditNoteShow extends Component
{
    use BuildsDocTimeline;
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public PurchaseCreditNote $note;

    public ?int $invoice_id = null;

    public string $allocation_amount = '';

    public function mount(PurchaseCreditNote $note): void
    {
        $company = $this->company();
        if ($company === null || $note->company_id !== $company->id) {
            abort(404);
        }
        $this->note = $note;
    }

    public function post(): void
    {
        abort_unless($this->note->status->isMutable(), 409);
        $actorId = auth()->id();
        try {
            app(ApApplicationService::class)->postCreditNote($this->note, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ApException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.credit_note.posted_success'));
        $this->redirectRoute('ap.credit-notes.show', $this->note->id, navigate: false);
    }

    public function allocate(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        abort_unless($this->note->status->isPosted(), 409);
        $this->validate([
            'invoice_id' => ['required', 'integer', Rule::exists('purchase_invoices', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('book_id', $book->id)->where('supplier_id', $this->note->supplier_id))],
            'allocation_amount' => 'required|numeric|gt:0',
        ]);
        try {
            $ap->allocateCreditNote($this->note, $ap->findInvoice($company, $book, (int) $this->invoice_id), $this->allocation_amount);
        } catch (PostingException|ApException $exception) {
            $this->addError('allocation', $exception->getMessage());

            return;
        }
        $this->note->refresh();
        $this->reset('invoice_id', 'allocation_amount');
        session()->flash('success', __('erp.note.allocated_success'));
    }

    public function render(): View
    {
        $this->note->loadMissing(['supplier', 'book', 'journal', 'lines', 'originalInvoice', 'allocations.invoice']);
        $openInvoices = PurchaseInvoice::query()->where('company_id', $this->note->company_id)->where('book_id', $this->note->book_id)
            ->where('supplier_id', $this->note->supplier_id)->where('status', 'posted')->orderBy('due_date')->get()
            ->filter(fn (PurchaseInvoice $invoice): bool => (float) $invoice->openBalance() > 0);

        return view('livewire.ap.note-show', [
            'note' => $this->note,
            'kind' => 'credit',
            'side' => 'ap',
            'dateField' => 'credit_note_date',
            'openInvoices' => $openInvoices,
            'timeline' => $this->docTimeline($this->note, withAllocations: true),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.credit_note.ap_title').' '.($this->note->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->note->loadMissing(['supplier', 'lines']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->note->number ?? __('erp.sales_invoice.draft_number'),
            __('erp.purchase_invoice.supplier') => $this->note->supplier?->legal_name,
            __('erp.date') => $this->exportDate($this->note->credit_note_date),
            __('erp.status') => $this->statusLabel($this->note->status),
            __('erp.currency') => $this->note->currency,
        ]);

        return [$this->excelSheetFrom(
            __('erp.export.sheet_lines'),
            [
                ['#', ExcelSheet::NUMBER, fn ($l) => $l->line_no],
                [__('erp.sales_invoice.line_description'), ExcelSheet::TEXT, fn ($l) => $l->description],
                [__('erp.document.tax'), ExcelSheet::MONEY, fn ($l) => $l->tax_amount],
                [__('erp.document.amount'), ExcelSheet::MONEY, fn ($l) => $l->net_amount],
            ],
            $this->note->lines,
            $meta,
            totals: [[
                null,
                __('erp.document.total'),
                $this->note->tax_total,
                $this->note->gross_total,
            ]],
            heading: __('erp.credit_note.ap_title').' '.($this->note->number ?? ''),
        )];
    }
}
