<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class FixedAssetCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $name = '';

    public string $cost = '';

    public int $useful_life_months = 60;

    public string $in_service_date = '';

    public string $location = '';

    public string $cost_account_code = '120104';

    public string $accum_account_code = '120105';

    public string $expense_account_code = '620402';

    public string $ap_account_code = '210103';

    public function mount(): void
    {
        $this->in_service_date = now()->toDateString();
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $accountRule = fn () => Rule::exists('accounts', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_posting', true));
        $settlementRule = Rule::exists('accounts', 'code')->where(fn ($query) => $query
            ->where('company_id', $company->id)
            ->where('is_posting', true)
            ->where('is_control', false));
        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('fixed_assets', 'code')->where('company_id', $company->id)],
            'name' => 'required|string|max:255', 'cost' => 'required|numeric|gt:0', 'useful_life_months' => 'required|integer|min:1|max:1200',
            'in_service_date' => 'required|date', 'location' => 'nullable|string|max:255',
            'cost_account_code' => ['required', $accountRule()], 'accum_account_code' => ['required', $accountRule()],
            'expense_account_code' => ['required', $accountRule()], 'ap_account_code' => ['required', $settlementRule],
        ]);
        try {
            $asset = $service->acquireAsset($company, $book, $this->only(['code', 'name', 'cost', 'useful_life_months', 'in_service_date', 'location', 'cost_account_code', 'accum_account_code', 'expense_account_code', 'ap_account_code']));
        } catch (PostingException $exception) {
            $this->addError('form', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.assets.created'));
        $this->redirectRoute('assets.show', $asset->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true)->orderBy('code')->get() : collect();
        $settlementAccounts = $accounts->where('is_control', false);

        return view('livewire.assets.fixed-asset-create', compact('accounts', 'settlementAccounts'));
    }
}
