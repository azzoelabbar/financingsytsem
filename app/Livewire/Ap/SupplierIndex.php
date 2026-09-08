<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SupplierIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $suppliers = $company
            ? app(ApApplicationService::class)->listSuppliers(
                $company,
                $this->listRequest(searchColumns: 'code,legal_name'),
            )
            : null;

        return view('livewire.ap.supplier-index', compact('suppliers'));
    }

    protected function excelTitle(): string
    {
        return __('erp.supplier.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $suppliers = app(ApApplicationService::class)->listSuppliers(
            $company,
            $this->exportRequest(searchColumns: 'code,legal_name'),
        );

        return [$this->excelSheetFrom(
            __('erp.supplier.title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($s) => $s->code],
                [__('erp.supplier.legal_name'), ExcelSheet::TEXT, fn ($s) => $s->legal_name],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($s) => $s->currency],
                [__('erp.status'), ExcelSheet::TEXT, fn ($s) => $s->is_active ? __('erp.active') : __('erp.inactive')],
            ],
            $suppliers,
        )];
    }
}
