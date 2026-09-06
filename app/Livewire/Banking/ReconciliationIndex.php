<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\BankReconciliation;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReconciliationIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, BankReconciliation>|null $reconciliations */
        $reconciliations = null;
        if ($company !== null) {
            $reconciliations = BankReconciliation::query()
                ->where('company_id', $company->id)
                ->with('treasuryAccount')
                ->orderByDesc('as_of_date')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.reconciliation-index', compact('reconciliations'));
    }
}
