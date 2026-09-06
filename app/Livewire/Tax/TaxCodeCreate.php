<?php

declare(strict_types=1);

namespace App\Livewire\Tax;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TaxCodeCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $name = '';

    public string $kind = 'output_vat';

    public string $gl_account_code = '';

    public bool $is_active = true;

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $this->validate(['code' => ['required', 'string', 'max:30', Rule::unique('tax_codes', 'code')->where('company_id', $company->id)], 'name' => 'required|string|max:255', 'kind' => ['required', Rule::in(['output_vat', 'input_vat', 'wht', 'cit', 'deferred'])], 'gl_account_code' => ['required', Rule::exists('accounts', 'code')->where(fn ($q) => $q->where('company_id', $company->id)->where('is_posting', true))], 'is_active' => 'boolean']);
        $service->defineTaxCode($company, $this->only(['code', 'name', 'kind', 'gl_account_code', 'is_active']));
        session()->flash('success', __('erp.tax.code_created'));
        $this->redirectRoute('tax.index', navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->orderBy('code')->get() : collect();

        return view('livewire.tax.tax-code-create', compact('accounts'));
    }
}
