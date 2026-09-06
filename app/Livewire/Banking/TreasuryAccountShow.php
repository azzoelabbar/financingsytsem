<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Treasury\TreasuryLedgerService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TreasuryAccountShow extends Component
{
    use InteractsWithAccountingContext;

    public TreasuryAccount $account;

    public function mount(TreasuryAccount $account): void
    {
        abort_unless($this->company()?->id === $account->company_id, 404);
        $this->account = $account;
    }

    public function render(TreasuryLedgerService $ledger): View
    {
        $this->account->loadMissing(['bank', 'transactions' => fn ($q) => $q->latest('transaction_date')->limit(20)]);

        return view('livewire.banking.treasury-account-show', ['account' => $this->account, 'bookBalance' => $ledger->bookBalance($this->account)]);
    }
}
