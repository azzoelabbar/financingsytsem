<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CreditNoteIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $notes = ($company && $book)
            ? app(ArApplicationService::class)->listCreditNotes($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.ar.credit-note-index', compact('notes'));
    }

    protected function excelTitle(): string
    {
        return __('erp.credit_note.ar_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $notes = app(ArApplicationService::class)->listCreditNotes(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.credit_note.ar_title'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($n) => $n->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.sales_invoice.customer'), ExcelSheet::TEXT, fn ($n) => $this->localisedName($n->customer)],
                [__('erp.date'), ExcelSheet::DATE, fn ($n) => $this->exportDate($n->credit_note_date)],
                [__('erp.credit_note.reason'), ExcelSheet::TEXT, fn ($n) => $n->reason],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($n) => $n->currency],
                [__('erp.total'), ExcelSheet::MONEY, fn ($n) => $n->gross_total],
                [__('erp.status'), ExcelSheet::TEXT, fn ($n) => $this->statusLabel($n->status)],
            ],
            $notes,
        )];
    }
}
