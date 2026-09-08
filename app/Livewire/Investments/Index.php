<?php

declare(strict_types=1);

namespace App\Livewire\Investments;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $investments = $company
            ? app(DomainApplicationService::class)->listInvestments($company, $this->listRequest(searchColumns: 'code'))
            : null;

        return view('livewire.investments.index', compact('investments'));
    }

    protected function excelTitle(): string
    {
        return __('erp.investment.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $investments = app(DomainApplicationService::class)->listInvestments(
            $company,
            $this->exportRequest(searchColumns: 'code'),
        );

        return [$this->excelSheetFrom(
            __('erp.investment.title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($i) => $i->code],
                [__('erp.investment.name'), ExcelSheet::TEXT, fn ($i) => $i->name],
                [__('erp.investment.classification'), ExcelSheet::TEXT, fn ($i) => __('erp.investment.classifications.'.$i->classification->value)],
                [__('erp.investment.cost'), ExcelSheet::MONEY, fn ($i) => $i->cost ?? $i->acquisition_cost],
                [__('erp.investment.carrying'), ExcelSheet::MONEY, fn ($i) => $i->carrying_amount ?? $i->carrying],
                [__('erp.investment.fair_value'), ExcelSheet::MONEY, fn ($i) => $i->fair_value],
                [__('erp.status'), ExcelSheet::TEXT, fn ($i) => $this->statusLabel($i->status)],
            ],
            $investments,
        )];
    }
}
