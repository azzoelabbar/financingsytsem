<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CustomerCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $name_ar = '';

    public string $currency = '';

    public function mount(): void
    {
        $company = $this->company();
        if ($this->currency === '' && $company !== null) {
            $this->currency = $company->functional_currency ?? '';
        }
    }

    public function save(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();

        $this->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'code')->where('company_id', $company->id),
            ],
            'name_ar' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
        ]);

        $attributes = [
            'code' => $this->code,
            'name_ar' => $this->name_ar,
        ];
        if ($this->currency !== '') {
            $attributes['currency'] = strtoupper($this->currency);
        }

        $customer = $ar->createCustomer($company, $attributes, auth()->id() !== null ? (int) auth()->id() : null);

        session()->flash('success', __('erp.success_created'));

        $this->redirectRoute('ar.customers.show', $customer, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.ar.customer-create', [
            'company' => $this->company(),
        ]);
    }
}
