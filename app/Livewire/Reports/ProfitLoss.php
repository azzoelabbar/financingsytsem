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
class ProfitLoss extends Component
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
            ? app(GlApplicationService::class)->profitAndLoss($company, $book, $this->asOf)
            : ['lines' => [], 'revenue' => '0', 'expenses' => '0', 'net_profit' => '0'];

        return view('livewire.reports.profit-loss', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.profit_loss');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(GlApplicationService::class)->profitAndLoss($company, $book, $this->asOf);

        $groups = [
            'revenue' => __('erp.statement.revenue'),
            'expense' => __('erp.statement.expenses'),
        ];

        return [
            $this->excelSheetFrom(
                __('erp.nav.profit_loss'),
                [
                    [__('erp.reports.section'), ExcelSheet::TEXT, fn ($l) => $groups[$l->group] ?? $l->group],
                    [__('erp.code'), ExcelSheet::TEXT, fn ($l) => $l->code],
                    [__('erp.name'), ExcelSheet::TEXT, fn ($l) => $l->nameAr],
                    [__('erp.amount'), ExcelSheet::MONEY, fn ($l) => $l->amount],
                ],
                $report['lines'] ?? [],
                $this->excelMeta([__('erp.aging.as_of') => $this->asOf]),
            ),
            $this->excelKeyValueSheet(__('erp.export.sheet_summary'), [
                __('erp.statement.revenue') => $report['revenue'] ?? '0',
                __('erp.statement.expenses') => $report['expenses'] ?? '0',
                __('erp.statement.net_income') => $report['net_profit'] ?? '0',
            ]),
        ];
    }
}
