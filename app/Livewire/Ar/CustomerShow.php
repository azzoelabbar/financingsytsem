<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CustomerShow extends Component
{
    use InteractsWithAccountingContext;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $company = $this->company();
        if ($company === null || $customer->company_id !== $company->id) {
            abort(404);
        }
        $this->customer = $customer;
    }

    public function render(): View
    {
        $company = $this->requireCompany();
        $book = $this->book();
        $ar = app(ArApplicationService::class);

        $statement = $book !== null ? $ar->customerStatement($this->customer, book: $book) : null;
        $invoices = null;
        $receipts = null;

        if ($book !== null) {
            $invoices = $ar->listInvoices($company, $book, Request::create('/', 'GET', [
                'customer_id' => $this->customer->id,
                'per_page' => 10,
            ]));
            $receipts = $ar->listReceipts($company, $book, Request::create('/', 'GET', [
                'customer_id' => $this->customer->id,
                'per_page' => 10,
            ]));
        }

        return view('livewire.ar.customer-show', [
            'statement' => $statement,
            'invoices' => $invoices,
            'receipts' => $receipts,
        ]);
    }
}
