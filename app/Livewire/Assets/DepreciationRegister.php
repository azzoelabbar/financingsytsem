<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Assets\FixedAsset;
use App\Services\Accounting\Support\Decimal;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DepreciationRegister extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        /** @var LengthAwarePaginator<int, FixedAsset>|null $assets */
        $assets = null;
        $totals = ['cost' => '0', 'accum' => '0', 'nbv' => '0'];

        if ($company !== null && $book !== null) {
            $base = FixedAsset::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
                });

            foreach ((clone $base)->get() as $asset) {
                $totals['cost'] = Decimal::add($totals['cost'], (string) $asset->cost);
                $totals['accum'] = Decimal::add($totals['accum'], (string) $asset->accum_depreciation);
                $totals['nbv'] = Decimal::add($totals['nbv'], $asset->netBookValue());
            }

            $assets = $base->orderBy('code')->paginate($this->perPage);
        }

        return view('livewire.assets.depreciation-register', compact('assets', 'totals'));
    }

    protected function excelTitle(): string
    {
        return __('erp.assets.depreciation');
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
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
            })
            ->orderBy('code')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        $totals = ['cost' => '0', 'accum' => '0', 'nbv' => '0'];

        foreach ($assets as $asset) {
            $totals['cost'] = Decimal::add($totals['cost'], (string) $asset->cost);
            $totals['accum'] = Decimal::add($totals['accum'], (string) $asset->accum_depreciation);
            $totals['nbv'] = Decimal::add($totals['nbv'], $asset->netBookValue());
        }

        return [$this->excelSheetFrom(
            __('erp.assets.depreciation'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($a) => $a->code],
                [__('erp.assets.name'), ExcelSheet::TEXT, fn ($a) => $a->name],
                [__('erp.assets.cost'), ExcelSheet::MONEY, fn ($a) => $a->cost],
                [__('erp.assets.accum_dep'), ExcelSheet::MONEY, fn ($a) => $a->accum_depreciation],
                [__('erp.assets.nbv'), ExcelSheet::MONEY, fn ($a) => $a->netBookValue()],
                [__('erp.status'), ExcelSheet::TEXT, fn ($a) => $this->statusLabel($a->status)],
            ],
            $assets,
            totals: [[
                __('erp.total'),
                null,
                $totals['cost'],
                $totals['accum'],
                $totals['nbv'],
                null,
            ]],
        )];
    }
}
