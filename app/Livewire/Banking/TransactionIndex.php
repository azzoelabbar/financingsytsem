<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\CashTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TransactionIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        /** @var LengthAwarePaginator<int, CashTransaction>|null $transactions */
        $transactions = null;
        if ($company !== null && $book !== null) {
            $transactions = CashTransaction::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->with('treasuryAccount')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('number', 'like', $needle)->orWhere('reference', 'like', $needle)->orWhere('description', 'like', $needle));
                })
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.transaction-index', compact('transactions'));
    }
}
