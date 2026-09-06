<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SupplierIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $suppliers = $company
            ? app(ApApplicationService::class)->listSuppliers(
                $company,
                $this->listRequest(searchColumns: 'code,legal_name'),
            )
            : null;

        return view('livewire.ap.supplier-index', compact('suppliers'));
    }
}
