<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Journal;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class JournalShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public Journal $journal;

    public function mount(Journal $journal): void
    {
        $company = $this->company();
        $book = $this->book();
        if ($company === null || $book === null
            || $journal->company_id !== $company->id
            || $journal->book_id !== $book->id) {
            abort(404);
        }
        $this->journal = app(GlApplicationService::class)->findJournal($company, $book, $journal->id);
    }

    public function render(): View
    {
        return view('livewire.gl.journal-show');
    }

    protected function excelTitle(): string
    {
        return __('erp.journal.title').' '.($this->journal->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->journal->loadMissing(['lines.account']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->journal->number,
            __('erp.journal.journal_date') => $this->exportDate($this->journal->journal_date),
            __('erp.status') => $this->statusLabel($this->journal->status),
        ]);

        return [$this->excelSheetFrom(
            __('erp.journal.entry_lines'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($l) => $l->account?->code],
                [__('erp.journal.account'), ExcelSheet::TEXT, fn ($l) => $l->account?->name_ar],
                [__('erp.journal.description'), ExcelSheet::TEXT, fn ($l) => $l->description],
                [__('erp.debit'), ExcelSheet::MONEY, fn ($l) => $l->debit],
                [__('erp.credit'), ExcelSheet::MONEY, fn ($l) => $l->credit],
            ],
            $this->journal->lines,
            $meta,
            totals: [[
                null,
                null,
                __('erp.total'),
                $this->journal->total_debit,
                $this->journal->total_credit,
            ]],
            heading: __('erp.journal.title').' '.($this->journal->number ?? ''),
        )];
    }
}
