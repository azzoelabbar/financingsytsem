<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\TreasuryAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TreasuryAccounts extends Component
{
    use InteractsWithAccountingContext;

    /** 'bank' | 'cash' */
    public string $type = 'bank';

    public function mount(string $type = 'bank'): void
    {
        $this->type = in_array($type, ['bank', 'cash'], true) ? $type : 'bank';
    }

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, TreasuryAccount>|null $accounts */
        $accounts = null;
        if ($company !== null) {
            $accounts = TreasuryAccount::query()
                ->where('company_id', $company->id)
                ->where('type', $this->type)
                ->with('bank')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name_ar', 'like', $needle)->orWhere('name_en', 'like', $needle));
                })
                ->orderBy('code')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.treasury-accounts', [
            'accounts' => $accounts,
            'type' => $this->type,
        ]);
    }
}
