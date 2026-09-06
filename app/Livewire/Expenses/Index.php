<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $expenses = ($company && $book)
            ? app(DomainApplicationService::class)->listExpenses($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.expenses.index', compact('expenses'));
    }
}
