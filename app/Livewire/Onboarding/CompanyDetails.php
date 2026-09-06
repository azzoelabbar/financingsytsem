<?php

declare(strict_types=1);

namespace App\Livewire\Onboarding;

use App\Support\Accounting\AccountingContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class CompanyDetails extends Component
{
    public string $name_ar = '';

    public string $name_en = '';

    public string $code = '';

    public string $country = 'LY';

    public string $tax_registration_number = '';

    public function mount(AccountingContext $context): void
    {
        // Already onboarded → straight to the ERP.
        $user = auth()->user();
        if ($user !== null && $context->companiesFor($user)->isNotEmpty()) {
            $this->redirectRoute('dashboard', navigate: false);

            return;
        }

        // Restore any in-progress entry so a refresh preserves state.
        /** @var array<string, string> $saved */
        $saved = session('onboarding.company', []);
        foreach ($saved as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = (string) $value;
            }
        }
    }

    public function updatedNameAr(string $value): void
    {
        if ($this->code === '') {
            $this->code = $this->suggestCode($value);
        }
    }

    public function continue(): void
    {
        $data = $this->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/'],
            'country' => ['required', 'string', Rule::in(['LY', 'TN', 'EG', 'DZ', 'MA', 'SA', 'AE', 'QA', 'KW', 'JO', 'US', 'GB'])],
            'tax_registration_number' => ['nullable', 'string', 'max:50'],
        ]);

        session(['onboarding.company' => $data]);

        $this->redirectRoute('onboarding.accounting', navigate: true);
    }

    private function suggestCode(string $name): string
    {
        $ascii = preg_replace('/[^A-Za-z0-9]+/', '', $name) ?? '';

        return $ascii !== '' ? strtoupper(substr($ascii, 0, 6)) : 'CO-001';
    }

    public function render(): View
    {
        return view('livewire.onboarding.company-details', [
            'countries' => ['LY' => 'ليبيا', 'TN' => 'تونس', 'EG' => 'مصر', 'DZ' => 'الجزائر', 'MA' => 'المغرب', 'SA' => 'السعودية', 'AE' => 'الإمارات', 'QA' => 'قطر', 'KW' => 'الكويت', 'JO' => 'الأردن', 'US' => 'United States', 'GB' => 'United Kingdom'],
        ]);
    }
}
