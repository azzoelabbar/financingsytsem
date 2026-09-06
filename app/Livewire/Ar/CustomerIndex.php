<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CustomerIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $customers = $company
            ? app(ArApplicationService::class)->listCustomers(
                $company,
                $this->listRequest(searchColumns: 'code,name_ar'),
            )
            : null;

        return view('livewire.ar.customer-index', compact('customers', 'company'));
    }
}
