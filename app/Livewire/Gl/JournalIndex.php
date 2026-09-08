<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class JournalIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $journals = ($company && $book)
            ? app(GlApplicationService::class)->listJournals($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.gl.journal-index', compact('journals'));
    }

    protected function excelTitle(): string
    {
        return __('erp.journal.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $journals = app(GlApplicationService::class)->listJournals(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.journal.title'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($j) => $j->number],
                [__('erp.journal.journal_date'), ExcelSheet::DATE, fn ($j) => $this->exportDate($j->journal_date)],
                [__('erp.debit'), ExcelSheet::MONEY, fn ($j) => $j->total_debit],
                [__('erp.credit'), ExcelSheet::MONEY, fn ($j) => $j->total_credit],
                [__('erp.status'), ExcelSheet::TEXT, fn ($j) => $this->statusLabel($j->status)],
            ],
            $journals,
        )];
    }
}
