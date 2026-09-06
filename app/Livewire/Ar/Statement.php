<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class Statement extends Component
{
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $customerId = null;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->requireCompany();

        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en']);

        $statement = null;
        if ($this->customerId !== null) {
            $customer = Customer::query()->where('company_id', $company->id)->find($this->customerId);
            if ($customer !== null) {
                $statement = app(ArApplicationService::class)->customerStatement($customer, Carbon::parse($this->asOf), $this->requireBook());
            }
        }

        return view('livewire.ar.statement', [
            'customers' => $customers,
            'statement' => $statement,
        ]);
    }
}
