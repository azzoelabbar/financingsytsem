<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class OpenItems extends Component
{
    use InteractsWithAccountingContext;

    #[Url]
    public ?int $customerId = null;

    public function render(): View
    {
        $company = $this->requireCompany();

        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'name_en']);

        $items = null;
        if ($this->customerId !== null) {
            $customer = Customer::query()->where('company_id', $company->id)->find($this->customerId);
            if ($customer !== null) {
                $items = app(ArApplicationService::class)->openItems($customer, book: $this->requireBook());
            }
        }

        return view('livewire.ar.open-items', [
            'customers' => $customers,
            'items' => $items,
        ]);
    }
}
