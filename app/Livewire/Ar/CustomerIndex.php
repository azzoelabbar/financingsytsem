<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CustomerIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $customers = $company
            ? app(ArApplicationService::class)->listCustomers(
                $company,
                $this->listRequest(searchColumns: 'code,name_ar'),
            )
            : null;

        return view('livewire.ar.customer-index', compact('customers', 'company'));
    }

    protected function excelTitle(): string
    {
        return __('erp.customer.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $customers = app(ArApplicationService::class)->listCustomers(
            $company,
            $this->exportRequest(searchColumns: 'code,name_ar'),
        );

        return [$this->excelSheetFrom(
            __('erp.customer.title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($c) => $c->code],
                [__('erp.name'), ExcelSheet::TEXT, fn ($c) => $this->localisedName($c)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($c) => $c->currency],
                [__('erp.status'), ExcelSheet::TEXT, fn ($c) => $c->is_active ? __('erp.active') : __('erp.inactive')],
            ],
            $customers,
        )];
    }
}
