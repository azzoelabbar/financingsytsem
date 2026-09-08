<?php

declare(strict_types=1);

namespace App\Livewire\Imports;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Inventory\InventoryItem;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class InventoryIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        abort_unless($this->context()->can('gl.read'), 403);
        $items = InventoryItem::query()->where('company_id', $this->requireCompany()->id)
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', '%'.$this->search.'%')->orWhere('name', 'like', '%'.$this->search.'%')))
            ->orderBy('code')->paginate(25);

        return view('livewire.imports.inventory', compact('items'));
    }

    protected function excelTitle(): string
    {
        return __('imports.kinds.items');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        abort_unless($this->context()->can('gl.read'), 403);

        $items = InventoryItem::query()
            ->where('company_id', $company->id)
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', '%'.$this->search.'%')->orWhere('name', 'like', '%'.$this->search.'%')))
            ->orderBy('code')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('imports.kinds.items'),
            [
                [__('imports.item_code'), ExcelSheet::TEXT, fn ($i) => $i->code],
                [__('imports.item_name'), ExcelSheet::TEXT, fn ($i) => $i->name],
                [__('imports.quantity'), ExcelSheet::NUMBER, fn ($i) => $i->quantity],
                [__('imports.value'), ExcelSheet::MONEY, fn ($i) => $i->value],
            ],
            $items,
            $this->excelMeta([
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
        )];
    }
}
