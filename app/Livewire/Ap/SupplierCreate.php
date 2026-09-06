<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SupplierCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $legal_name = '';

    public string $currency = '';

    public function mount(): void
    {
        $company = $this->company();
        if ($this->currency === '' && $company !== null) {
            $this->currency = $company->functional_currency ?? '';
        }
    }

    public function save(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();

        $this->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->where('company_id', $company->id),
            ],
            'legal_name' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
        ]);

        $attributes = ['code' => $this->code, 'legal_name' => $this->legal_name];
        if ($this->currency !== '') {
            $attributes['currency'] = strtoupper($this->currency);
        }

        $ap->createSupplier($company, $attributes, auth()->id() !== null ? (int) auth()->id() : null);

        session()->flash('success', __('erp.success_created'));
        $this->redirectRoute('ap.suppliers', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.ap.supplier-create', [
            'company' => $this->company(),
        ]);
    }
}
