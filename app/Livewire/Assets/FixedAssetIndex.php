<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Assets\FixedAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class FixedAssetIndex extends Component
{
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
}
