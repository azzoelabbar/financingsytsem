<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Enums\Treasury\CashTransactionType;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Treasury\CashTransactionService;
use App\Services\Treasury\Exceptions\TreasuryException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TransactionCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $treasury_account_id = null;

    public string $type = 'bank_receipt';

    public string $transaction_date = '';

    public string $amount = '';

    public string $currency = 'LYD';

    public string $exchange_rate = '1';

    public string $counter_account_code = '410101';

    public ?int $counter_treasury_account_id = null;

    public string $reference = '';

    public string $description = '';

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
    }

    public function updatedTreasuryAccountId(): void
    {
        $account = $this->company() && $this->treasury_account_id ? TreasuryAccount::query()->where('company_id', $this->company()->id)->find($this->treasury_account_id) : null;
        if ($account) {
            $this->currency = $account->currency;
        }
    }

    public function save(CashTransactionService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $isTransfer = $this->type === 'transfer';
        $this->validate(['treasury_account_id' => ['required', 'integer', Rule::exists('treasury_accounts', 'id')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_active', true))],
            'type' => ['required', Rule::enum(CashTransactionType::class)], 'transaction_date' => 'required|date', 'amount' => 'required|numeric|gt:0', 'currency' => 'required|string|size:3', 'exchange_rate' => 'required|numeric|gt:0',
            'counter_account_code' => $isTransfer ? 'nullable' : ['required', 'string', Rule::exists('accounts', 'code')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_posting', true))],
            'counter_treasury_account_id' => $isTransfer ? ['required', 'integer', 'different:treasury_account_id', Rule::exists('treasury_accounts', 'id')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_active', true))] : 'nullable',
            'reference' => 'nullable|string|max:255', 'description' => 'nullable|string|max:500']);
        $account = TreasuryAccount::query()->where('company_id', $company->id)->findOrFail($this->treasury_account_id);
        try {
            $tx = $service->createDraft($company, $book, $account, CashTransactionType::from($this->type), $this->amount, ['transaction_date' => $this->transaction_date, 'currency' => strtoupper($this->currency), 'exchange_rate' => $this->exchange_rate, 'counter_account_code' => $isTransfer ? null : $this->counter_account_code, 'counter_treasury_account_id' => $isTransfer ? $this->counter_treasury_account_id : null, 'reference' => $this->reference ?: null, 'description' => $this->description ?: null, 'created_by' => auth()->id()]);
        } catch (PostingException|TreasuryException $exception) {
            $this->addError('transaction', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.banking.tx_created'));
        $this->redirectRoute('banking.transactions.show', $tx->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company ? TreasuryAccount::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get() : collect();
        $counterAccounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true)->orderBy('code')->get() : collect();

        return view('livewire.banking.transaction-create', ['accounts' => $accounts, 'counterAccounts' => $counterAccounts, 'types' => CashTransactionType::cases()]);
    }
}
