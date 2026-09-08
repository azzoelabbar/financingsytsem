<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\Supplier;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class OpenItems extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $supplierId = null;

    public function render(): View
    {
        $company = $this->requireCompany();

        $suppliers = Supplier::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'legal_name', 'name_ar', 'trading_name']);

        $items = null;
        if ($this->supplierId !== null) {
            $supplier = Supplier::query()->where('company_id', $company->id)->find($this->supplierId);
            if ($supplier !== null) {
                $items = app(ApApplicationService::class)->openItems($supplier, book: $this->requireBook());
            }
        }

        return view('livewire.ap.open-items', [
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.open_items.ap_title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null || $this->supplierId === null) {
            return [];
        }

        $supplier = Supplier::query()->where('company_id', $company->id)->find($this->supplierId);

        if ($supplier === null) {
            return [];
        }

        $items = app(ApApplicationService::class)->openItems($supplier, book: $book);

        return [$this->excelSheetFrom(
            __('erp.open_items.ap_title'),
            [
                [__('erp.open_items.document'), ExcelSheet::TEXT, fn (array $i) => $i['number'] ?? null],
                [__('erp.open_items.doc_type'), ExcelSheet::TEXT, fn (array $i) => __('erp.open_items.types.'.$i['type'])],
                [__('erp.date'), ExcelSheet::DATE, fn (array $i) => $this->exportDate($i['date'] ?? null)],
                [__('erp.currency'), ExcelSheet::TEXT, fn (array $i) => $i['currency'] ?? null],
                [__('erp.open_items.original'), ExcelSheet::MONEY, fn (array $i) => $i['amount'] ?? null],
                [__('erp.open_items.open'), ExcelSheet::MONEY, fn (array $i) => $i['open'] ?? null],
            ],
            $items,
            $this->excelMeta([
                __('erp.supplier.title') => $supplier->code.' - '.$supplier->legal_name,
            ]),
        )];
    }
}
