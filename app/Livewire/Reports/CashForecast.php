<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CashForecast extends Component
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

        $report = $company && $book
            ? app(DomainApplicationService::class)->cashForecast($company, $book, $this->asOf)
            : ['inflows' => '0', 'outflows' => '0', 'net' => '0', 'ar' => null, 'ap' => null];

        return view('livewire.reports.cash-forecast', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.reports.cash_forecast');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(DomainApplicationService::class)->cashForecast($company, $book, $this->asOf);
        $meta = $this->excelMeta([__('erp.aging.as_of') => $this->asOf]);
        $buckets = ['current', '1_30', '31_60', '61_90'];

        $expectedIn = [];
        $expectedOut = [];

        foreach ($buckets as $bucket) {
            $expectedIn[__('erp.aging.'.$bucket)] = $report['ar']['buckets'][$bucket] ?? '0';
            $expectedOut[__('erp.aging.'.$bucket)] = $report['ap']['buckets'][$bucket] ?? '0';
        }

        return [
            $this->excelKeyValueSheet(__('erp.export.sheet_summary'), [
                __('erp.reports.cash_in') => $report['inflows'] ?? '0',
                __('erp.reports.cash_out') => $report['outflows'] ?? '0',
                __('erp.reports.net_cash_change') => $report['net'] ?? '0',
            ], meta: $meta),
            $this->excelKeyValueSheet(__('erp.reports.expected_from_customers'), $expectedIn, meta: $meta),
            $this->excelKeyValueSheet(__('erp.reports.expected_to_suppliers'), $expectedOut, meta: $meta),
        ];
    }
}
