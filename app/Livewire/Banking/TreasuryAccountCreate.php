<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Treasury\BankAccountService;
use App\Services\Treasury\Exceptions\TreasuryException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TreasuryAccountCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $type = 'bank';

    public string $code = '';

    public string $name_ar = '';

    public string $name_en = '';

    public string $currency = 'LYD';

    public string $gl_account_code = '';

    public string $account_number = '';

    public string $iban = '';

    public string $bank_code = '';

    public string $bank_name_ar = '';

    public string $bank_name_en = '';

    public string $swift = '';

    public function mount(string $type = 'bank'): void
    {
        $this->type = in_array($type, ['bank', 'cash'], true) ? $type : 'bank';
        $this->gl_account_code = $this->type === 'bank' ? '110102' : '110101';
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
    }

    public function save(BankAccountService $service): void
    {
        $company = $this->requireCompany();
        $rules = [
            'code' => ['required', 'string', 'max:50', Rule::unique('treasury_accounts', 'code')->where(fn ($q) => $q->where('company_id', $company->id))],
            'name_ar' => 'required|string|max:255', 'name_en' => 'nullable|string|max:255', 'currency' => 'required|string|size:3',
            'gl_account_code' => ['required', 'string', Rule::exists('accounts', 'code')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_posting', true))],
            'account_number' => 'nullable|string|max:100', 'iban' => 'nullable|string|max:100',
        ];
        if ($this->type === 'bank') {
            $rules += ['bank_code' => ['required', 'string', 'max:50', Rule::unique('banks', 'code')->where(fn ($q) => $q->where('company_id', $company->id))], 'bank_name_ar' => 'required|string|max:255', 'bank_name_en' => 'nullable|string|max:255', 'swift' => 'nullable|string|max:50'];
        }
        $this->validate($rules);
        try {
            $bank = $this->type === 'bank' ? $service->createBank($company, ['code' => $this->bank_code, 'name_ar' => $this->bank_name_ar, 'name_en' => $this->bank_name_en ?: null, 'swift' => $this->swift ?: null]) : null;
            $account = $service->createAccount($company, ['bank_id' => $bank?->id, 'code' => $this->code, 'name_ar' => $this->name_ar, 'name_en' => $this->name_en ?: null, 'type' => $this->type, 'account_number' => $this->account_number ?: null, 'iban' => $this->iban ?: null, 'currency' => strtoupper($this->currency), 'gl_account_code' => $this->gl_account_code, 'opening_balance' => '0']);
        } catch (PostingException|TreasuryException $exception) {
            $this->addError('account', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.banking.account_created'));
        $this->redirectRoute('banking.accounts.show', $account->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $glAccounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true)
            ->where(fn ($q) => $q->where('is_bank_account', true)->orWhere('code', 'like', '1101%'))->orderBy('code')->get() : collect();

        return view('livewire.banking.treasury-account-create', compact('glAccounts'));
    }
}
