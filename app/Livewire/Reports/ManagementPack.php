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
class ManagementPack extends Component
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
        $pack = ($company && $book)
            ? app(DomainApplicationService::class)->managementPack($company, $book, $this->asOf)
            : [];

        return view('livewire.reports.management-pack', [
            'pack' => $pack,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.management_pack');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $pack = app(DomainApplicationService::class)->managementPack($company, $book, $this->asOf);
        $meta = $this->excelMeta([__('erp.aging.as_of') => $this->asOf]);

        $balanceSheet = $pack['balance_sheet']['totals'] ?? [];
        $incomeStatement = $pack['income_statement'] ?? [];
        $cashFlow = $pack['cash_flow'] ?? [];

        return [
            $this->excelKeyValueSheet(__('erp.nav.balance_sheet'), [
                __('erp.reports.total_assets') => $balanceSheet['assets'] ?? '0',
                __('erp.reports.total_liabilities') => $balanceSheet['liabilities'] ?? '0',
                __('erp.reports.total_equity') => $balanceSheet['equity'] ?? '0',
                __('erp.statement.current_result') => $balanceSheet['net_result'] ?? '0',
            ], meta: $meta),
            $this->excelKeyValueSheet(__('erp.nav.profit_loss'), [
                __('erp.statement.revenue') => $incomeStatement['revenue'] ?? '0',
                __('erp.statement.expenses') => $incomeStatement['expenses'] ?? '0',
                __('erp.statement.net_income') => $incomeStatement['net_profit'] ?? '0',
            ], meta: $meta),
            $this->excelKeyValueSheet(__('erp.nav.cash_flow'), [
                __('erp.reports.activity_operating') => $cashFlow['operating'] ?? '0',
                __('erp.reports.activity_investing') => $cashFlow['investing'] ?? '0',
                __('erp.reports.activity_financing') => $cashFlow['financing'] ?? '0',
                __('erp.reports.net_cash_change') => $cashFlow['net'] ?? '0',
            ], meta: $meta),
        ];
    }
}
