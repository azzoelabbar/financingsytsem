<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\CashTransaction;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Treasury\CashTransactionService;
use App\Services\Treasury\Exceptions\TreasuryException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TransactionShow extends Component
{
    use BuildsDocTimeline;
    use InteractsWithAccountingContext;

    public CashTransaction $transaction;

    public function mount(CashTransaction $transaction): void
    {
        abort_unless($this->company()?->id === $transaction->company_id, 404);
        $this->transaction = $transaction;
    }

    public function post(CashTransactionService $service): void
    {
        abort_unless($this->transaction->status->isMutable(), 409);
        $actorId = auth()->id();
        $actorId = is_int($actorId) ? $actorId : null;
        try {
            $service->post($this->transaction, $actorId);
        } catch (PostingException|TreasuryException $e) {
            $this->addError('posting', $e->getMessage());

            return;
        }session()->flash('success', __('erp.banking.tx_posted'));
        $this->redirectRoute('banking.transactions.show', $this->transaction->id, navigate: false);
    }

    public function render(): View
    {
        $this->transaction->loadMissing(['treasuryAccount', 'counterTreasuryAccount', 'book', 'journal']);

        return view('livewire.banking.transaction-show', ['tx' => $this->transaction, 'timeline' => $this->docTimeline($this->transaction)]);
    }
}
