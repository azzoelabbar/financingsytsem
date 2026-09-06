<?php

declare(strict_types=1);

namespace App\Livewire\Onboarding;

use App\Enums\Accounting\AccountingFramework;
use App\Models\Accounting\Currency;
use App\Services\Onboarding\CompanyProvisioningService;
use App\Support\Accounting\AccountingContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class AccountingSetup extends Component
{
    public string $functional_currency = 'LYD';

    public string $presentation_currency = 'LYD';

    public string $accounting_framework = 'local_gaap';

    public int $fiscal_year = 0;

    public bool $provisioning = false;

    public function mount(AccountingContext $context): void
    {
        $user = auth()->user();
        if ($user !== null && $context->companiesFor($user)->isNotEmpty()) {
            $this->redirectRoute('dashboard', navigate: false);

            return;
        }

        // Company step must be completed first.
        if (session('onboarding.company') === null) {
            $this->redirectRoute('onboarding.company', navigate: false);

            return;
        }

        $this->fiscal_year = (int) now()->year;

        /** @var array<string, string> $saved */
        $saved = session('onboarding.accounting', []);
        foreach ($saved as $key => $value) {
            if (property_exists($this, $key) && $key !== 'provisioning') {
                $this->{$key} = $key === 'fiscal_year' ? (int) $value : (string) $value;
            }
        }
    }

    public function finish(AccountingContext $context): void
    {
        $data = $this->validate([
            'functional_currency' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'presentation_currency' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'accounting_framework' => ['required', Rule::enum(AccountingFramework::class)],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $user = auth()->user();
        abort_if($user === null, 403);

        /** @var array{name_ar:string, name_en?:?string, code:string, country:string, tax_registration_number?:?string}|null $company */
        $company = session('onboarding.company');
        if ($company === null) {
            $this->redirectRoute('onboarding.company', navigate: false);

            return;
        }

        $this->provisioning = true;

        $created = app(CompanyProvisioningService::class)->provision($user, $company, $data);

        // Select the freshly created company + clear the wizard state.
        $context->setCompany($created->id);
        session()->forget(['onboarding.company', 'onboarding.accounting']);

        session()->flash('success', __('erp.onboarding.completed_success'));

        $this->redirectRoute('dashboard', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.onboarding.accounting-setup', [
            'currencies' => Currency::query()->where('is_active', true)->orderBy('code')->get(),
            'frameworks' => AccountingFramework::cases(),
        ]);
    }
}
