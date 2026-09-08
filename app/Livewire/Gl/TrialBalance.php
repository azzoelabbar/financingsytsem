<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Services\Accounting\Data\TrialBalanceRow;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TrialBalance extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $report = ($company && $book)
            ? app(GlApplicationService::class)->trialBalance($company, $book, Carbon::parse($this->asOf))
            : ['rows' => [], 'totals' => ['debit' => '0', 'credit' => '0', 'balanced' => true]];

        return view('livewire.gl.trial-balance', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.trial_balance');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(GlApplicationService::class)->trialBalance($company, $book, Carbon::parse($this->asOf));
        $totals = $report['totals'] ?? [];

        return [$this->excelSheetFrom(
            __('erp.nav.trial_balance'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn (TrialBalanceRow $r) => $r->code],
                [__('erp.name'), ExcelSheet::TEXT, fn (TrialBalanceRow $r) => $r->nameAr],
                [__('erp.debit'), ExcelSheet::MONEY, fn (TrialBalanceRow $r) => $r->debit],
                [__('erp.credit'), ExcelSheet::MONEY, fn (TrialBalanceRow $r) => $r->credit],
                [__('erp.balance'), ExcelSheet::MONEY, fn (TrialBalanceRow $r) => $r->balance],
            ],
            $report['rows'] ?? [],
            $this->excelMeta([
                __('erp.aging.as_of') => $this->asOf,
                __('erp.status') => ($totals['balanced'] ?? false) ? __('erp.balanced') : __('erp.unbalanced'),
            ]),
            totals: [[
                __('erp.total'),
                null,
                $totals['debit'] ?? '0',
                $totals['credit'] ?? '0',
                null,
            ]],
        )];
    }
}
