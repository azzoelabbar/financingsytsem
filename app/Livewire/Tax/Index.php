<?php

declare(strict_types=1);

namespace App\Livewire\Tax;

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
        $taxCodes = $company
            ? app(DomainApplicationService::class)->listTaxCodes($company, $this->listRequest(searchColumns: 'code,name'))
            : null;

        return view('livewire.tax.index', compact('taxCodes'));
    }

    protected function excelTitle(): string
    {
        return __('erp.tax.codes_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $taxCodes = app(DomainApplicationService::class)->listTaxCodes(
            $company,
            $this->exportRequest(searchColumns: 'code,name'),
        );

        return [$this->excelSheetFrom(
            __('erp.tax.codes_title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($c) => $c->code],
                [__('erp.name'), ExcelSheet::TEXT, fn ($c) => $c->name],
                [__('erp.tax.kind'), ExcelSheet::TEXT, fn ($c) => __('erp.tax.kinds.'.$c->kind)],
                [__('erp.tax.gl_account'), ExcelSheet::TEXT, fn ($c) => $c->gl_account_code],
                [__('erp.status'), ExcelSheet::TEXT, fn ($c) => $c->is_active ? __('erp.active') : __('erp.inactive')],
            ],
            $taxCodes,
        )];
    }
}
