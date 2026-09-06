<?php

declare(strict_types=1);

namespace App\Livewire\Books;

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
        $books = $company
            ? app(DomainApplicationService::class)->listBooks($company)
            : [];

        return view('livewire.books.index', [
            'books' => $books,
            'company' => $company,
            'currentBook' => $this->book(),
        ]);
    }
}
