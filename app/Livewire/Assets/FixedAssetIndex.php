<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Assets\FixedAsset;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class FixedAssetIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    /** active | disposed | '' (all) */
    #[Url]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        /** @var LengthAwarePaginator<int, FixedAsset>|null $assets */
        $assets = null;
        if ($company !== null && $book !== null) {
            $assets = FixedAsset::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
                })
                ->orderBy('code')
                ->paginate($this->perPage);
        }

        return view('livewire.assets.fixed-asset-index', compact('assets'));
    }

    protected function excelTitle(): string
    {
        return __('erp.assets.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $assets = FixedAsset::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
            })
            ->orderBy('code')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('erp.assets.title'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($a) => $a->code],
                [__('erp.assets.name'), ExcelSheet::TEXT, fn ($a) => $a->name],
                [__('erp.assets.in_service'), ExcelSheet::DATE, fn ($a) => $this->exportDate($a->in_service_date)],
                [__('erp.assets.cost'), ExcelSheet::MONEY, fn ($a) => $a->cost],
                [__('erp.assets.accum_dep'), ExcelSheet::MONEY, fn ($a) => $a->accum_depreciation],
                [__('erp.assets.nbv'), ExcelSheet::MONEY, fn ($a) => $a->netBookValue()],
                [__('erp.status'), ExcelSheet::TEXT, fn ($a) => $this->statusLabel($a->status)],
            ],
            $assets,
            $this->excelMeta([
                __('erp.status') => $this->status !== '' ? $this->statusLabel($this->status) : null,
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
        )];
    }
}
