<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class BalanceSheet extends Component
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
            ? app(GlApplicationService::class)->balanceSheet($company, $book, $this->asOf)
            : ['lines' => [], 'totals' => [], 'balanced' => true];

        return view('livewire.reports.balance-sheet', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.balance_sheet');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(GlApplicationService::class)->balanceSheet($company, $book, $this->asOf);
        $totals = $report['totals'] ?? [];

        $groups = [
            'asset' => __('erp.statement.assets'),
            'liability' => __('erp.statement.liabilities'),
            'equity' => __('erp.statement.equity'),
        ];

        return [
            $this->excelSheetFrom(
                __('erp.nav.balance_sheet'),
                [
                    [__('erp.reports.section'), ExcelSheet::TEXT, fn ($l) => $groups[$l->group] ?? $l->group],
                    [__('erp.code'), ExcelSheet::TEXT, fn ($l) => $l->code],
                    [__('erp.name'), ExcelSheet::TEXT, fn ($l) => $l->nameAr],
                    [__('erp.amount'), ExcelSheet::MONEY, fn ($l) => $l->amount],
                ],
                $report['lines'] ?? [],
                $this->excelMeta([
                    __('erp.aging.as_of') => $this->asOf,
                    __('erp.status') => ($report['balanced'] ?? false) ? __('erp.balanced') : __('erp.unbalanced'),
                ]),
            ),
            $this->excelKeyValueSheet(__('erp.export.sheet_summary'), [
                __('erp.reports.total_assets') => $totals['assets'] ?? '0',
                __('erp.reports.total_liabilities') => $totals['liabilities'] ?? '0',
                __('erp.reports.total_equity') => $totals['equity'] ?? '0',
                __('erp.statement.current_result') => $totals['net_result'] ?? '0',
            ]),
        ];
    }
}
