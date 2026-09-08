<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DebitNoteIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $notes = ($company && $book)
            ? app(ApApplicationService::class)->listDebitNotes($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.ap.debit-note-index', compact('notes'));
    }

    protected function excelTitle(): string
    {
        return __('erp.debit_note.ap_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $notes = app(ApApplicationService::class)->listDebitNotes(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.debit_note.ap_title'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($n) => $n->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.purchase_invoice.supplier'), ExcelSheet::TEXT, fn ($n) => $n->supplier?->legal_name],
                [__('erp.date'), ExcelSheet::DATE, fn ($n) => $this->exportDate($n->debit_note_date)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($n) => $n->currency],
                [__('erp.total'), ExcelSheet::MONEY, fn ($n) => $n->gross_total],
                [__('erp.status'), ExcelSheet::TEXT, fn ($n) => $this->statusLabel($n->status)],
            ],
            $notes,
        )];
    }
}
