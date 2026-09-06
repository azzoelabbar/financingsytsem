<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SupplierPaymentIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $payments = ($company && $book)
            ? app(ApApplicationService::class)->listPayments($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.ap.supplier-payment-index', compact('payments'));
    }
}
