<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class GeneralLedger extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    #[Url(as: 'account')]
    public string $accountCode = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updatedAccountCode(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        $lines = ($company && $book)
            ? app(GlApplicationService::class)->generalLedger($company, $book, $this->listRequest([
                'account_code' => $this->accountCode !== '' ? $this->accountCode : null,
                'from' => $this->from !== '' ? $this->from : null,
                'to' => $this->to !== '' ? $this->to : null,
            ]))
            : null;

        return view('livewire.gl.general-ledger', compact('lines'));
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.gl_ledger');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $lines = app(GlApplicationService::class)->generalLedger($company, $book, $this->exportRequest([
            'account_code' => $this->accountCode !== '' ? $this->accountCode : null,
            'from' => $this->from !== '' ? $this->from : null,
            'to' => $this->to !== '' ? $this->to : null,
        ]));

        return [$this->excelSheetFrom(
            __('erp.nav.gl_ledger'),
            [
                [__('erp.date'), ExcelSheet::DATE, fn ($l) => $this->exportDate($l->journal?->journal_date)],
                [__('erp.journal.title'), ExcelSheet::TEXT, fn ($l) => $l->journal?->number],
                [__('erp.code'), ExcelSheet::TEXT, fn ($l) => $l->account?->code],
                [__('erp.journal.account'), ExcelSheet::TEXT, fn ($l) => $l->account?->name_ar],
                [__('erp.journal.description'), ExcelSheet::TEXT, fn ($l) => $l->description ?? $l->memo],
                [__('erp.debit'), ExcelSheet::MONEY, fn ($l) => $l->debit],
                [__('erp.credit'), ExcelSheet::MONEY, fn ($l) => $l->credit],
            ],
            $lines,
            $this->excelMeta([
                __('erp.gl_ledger.account_code') => $this->accountCode !== '' ? $this->accountCode : null,
                __('erp.gl_ledger.from') => $this->from !== '' ? $this->from : null,
                __('erp.gl_ledger.to') => $this->to !== '' ? $this->to : null,
            ]),
        )];
    }
}
