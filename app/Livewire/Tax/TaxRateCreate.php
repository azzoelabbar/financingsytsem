<?php

declare(strict_types=1);

namespace App\Livewire\Tax;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Tax\TaxCode;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TaxRateCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $tax_code_id = null;

    public string $rate = '';

    public string $effective_from = '';

    public string $effective_to = '';

    public string $legal_reference = '';

    public string $authority = '';

    public function mount(): void
    {
        $this->effective_from = now()->toDateString();
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $this->validate(['tax_code_id' => ['required', Rule::exists('tax_codes', 'id')->where('company_id', $company->id)], 'rate' => 'required|numeric|min:0|max:100', 'effective_from' => 'required|date', 'effective_to' => 'nullable|date|after_or_equal:effective_from', 'legal_reference' => 'required|string|max:255', 'authority' => 'nullable|string|max:255']);
        $code = TaxCode::query()->where('company_id', $company->id)->findOrFail($this->tax_code_id);
        try {
            $service->approveTaxRate($code, $this->only(['rate', 'effective_from', 'effective_to', 'legal_reference', 'authority']));
        } catch (PostingException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }
        session()->flash('success', __('erp.tax.rate_created'));
        $this->redirectRoute('tax.rules', navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $codes = $company ? TaxCode::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get() : collect();

        return view('livewire.tax.tax-rate-create', compact('codes'));
    }
}
