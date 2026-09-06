<?php

declare(strict_types=1);

namespace App\Livewire\OpeningBalances;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class OpeningBalanceCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $as_of = '';

    public string $currency = 'LYD';

    public function mount(): void
    {
        $this->as_of = now()->startOfYear()->toDateString();
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate(['as_of' => ['required', 'date', Rule::unique('opening_balance_batches', 'as_of')->where(fn ($q) => $q->where('company_id', $company->id)->where('book_id', $book->id))], 'currency' => 'required|string|size:3']);
        try {
            $batch = $service->createOpeningBatch($company, $book, $this->as_of, strtoupper($this->currency));
        } catch (PostingException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }
        session()->flash('success', __('erp.opening.created'));
        $this->redirectRoute('opening-balances.show', $batch->id, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.opening-balances.opening-balance-create');
    }
}
