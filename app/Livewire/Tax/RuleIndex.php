<?php

declare(strict_types=1);

namespace App\Livewire\Tax;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Tax\TaxRate;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class RuleIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, TaxRate>|null $rates */
        $rates = null;
        if ($company !== null) {
            $rates = TaxRate::query()
                ->whereHas('taxCode', fn ($q) => $q->where('company_id', $company->id))
                ->with('taxCode')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->whereHas('taxCode', fn ($c) => $c->where('code', 'like', $needle)->orWhere('name', 'like', $needle));
                })
                ->orderByDesc('effective_from')
                ->paginate($this->perPage);
        }

        return view('livewire.tax.rule-index', compact('rates'));
    }
}
