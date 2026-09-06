<?php

declare(strict_types=1);

namespace App\Livewire\OpeningBalances;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Gl\OpeningBalanceBatch;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class OpeningBalanceShow extends Component
{
    use InteractsWithAccountingContext;

    public OpeningBalanceBatch $batch;

    public string $accountCode = '';

    public string $side = 'debit';

    public string $amount = '';

    public function mount(OpeningBalanceBatch $batch): void
    {
        $company = $this->company();
        if ($company === null || $batch->company_id !== $company->id) {
            abort(404);
        }
        $this->batch = $batch;
    }

    public function validateBatch(): void
    {
        $this->run(fn ($s) => $s->validateOpeningBatch($this->batch), 'opening.validated');
    }

    public function addLine(): void
    {
        $company = $this->requireCompany();
        $this->validate(['accountCode' => ['required', Rule::exists('accounts', 'code')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_posting', true))], 'side' => ['required', Rule::in(['debit', 'credit'])], 'amount' => 'required|numeric|gt:0']);
        try {
            $line = $this->side === 'debit'
                ? ['account' => $this->accountCode, 'debit' => $this->amount]
                : ['account' => $this->accountCode, 'credit' => $this->amount];
            app(DomainApplicationService::class)->addOpeningLine($this->batch, $line);
            $this->batch->refresh();
            $this->accountCode = '';
            $this->amount = '';
            session()->flash('success', __('erp.opening.line_added'));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function postBatch(): void
    {
        $this->run(fn ($s) => $s->postOpeningBatch($this->batch), 'opening.posted');
    }

    public function lockBatch(): void
    {
        $this->run(fn ($s) => $s->lockOpeningBatch($this->batch), 'opening.locked');
    }

    private function run(callable $action, string $messageKey): void
    {
        try {
            $action(app(DomainApplicationService::class));
            session()->flash('success', __('erp.'.$messageKey));
            $this->redirectRoute('opening-balances.show', $this->batch->id, navigate: false);
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $this->batch->loadMissing(['lines', 'journal']);

        $totalDebit = '0';
        $totalCredit = '0';
        foreach ($this->batch->lines as $line) {
            $totalDebit = Decimal::add($totalDebit, (string) $line->debit);
            $totalCredit = Decimal::add($totalCredit, (string) $line->credit);
        }

        $company = $this->company();
        $accounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('opening_balance_allowed', true)->orderBy('code')->get() : collect();

        return view('livewire.opening-balances.opening-balance-show', [
            'batch' => $this->batch,
            'status' => (string) $this->batch->status,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'difference' => Decimal::sub($totalDebit, $totalCredit),
            'accounts' => $accounts,
        ]);
    }
}
