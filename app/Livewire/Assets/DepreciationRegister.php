<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Assets\FixedAsset;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DepreciationRegister extends Component
{
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
}
