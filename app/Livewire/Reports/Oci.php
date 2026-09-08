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
class Oci extends Component
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
            ? app(DomainApplicationService::class)->otherComprehensiveIncome($company, $book, $this->asOf)
            : ['lines' => [], 'total' => '0'];

        return view('livewire.reports.oci', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.reports.oci');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(DomainApplicationService::class)->otherComprehensiveIncome($company, $book, $this->asOf);

        return [$this->excelSheetFrom(
            __('erp.reports.oci'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn (array $l) => $l['code'] ?? null],
                [__('erp.name'), ExcelSheet::TEXT, fn (array $l) => $l['name'] ?? null],
                [__('erp.amount'), ExcelSheet::MONEY, fn (array $l) => $l['amount'] ?? null],
            ],
            $report['lines'] ?? [],
            $this->excelMeta([__('erp.aging.as_of') => $this->asOf]),
            totals: [[__('erp.total'), null, $report['total'] ?? '0']],
        )];
    }
}
