<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseDebitNote;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DebitNoteShow extends Component
{
    use BuildsDocTimeline;
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public PurchaseDebitNote $note;

    public function mount(PurchaseDebitNote $note): void
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
            app(ApApplicationService::class)->postDebitNote($this->note, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ApException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.debit_note.posted_success'));
        $this->redirectRoute('ap.debit-notes.show', $this->note->id, navigate: false);
    }

    public function render(): View
    {
        $this->note->loadMissing(['supplier', 'book', 'journal', 'lines', 'originalInvoice']);

        return view('livewire.ap.note-show', [
            'note' => $this->note,
            'kind' => 'debit',
            'side' => 'ap',
            'dateField' => 'debit_note_date',
            'timeline' => $this->docTimeline($this->note),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.debit_note.ap_title').' '.($this->note->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->note->loadMissing(['supplier', 'lines']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->note->number ?? __('erp.sales_invoice.draft_number'),
            __('erp.purchase_invoice.supplier') => $this->note->supplier?->legal_name,
            __('erp.date') => $this->exportDate($this->note->debit_note_date),
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
            heading: __('erp.debit_note.ap_title').' '.($this->note->number ?? ''),
        )];
    }
}
