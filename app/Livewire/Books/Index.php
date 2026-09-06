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

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $books = array_values(array_filter($books, function ($book) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $book->code,
                    $book->name_ar,
                    $book->name_en,
                ])));

                return str_contains($haystack, $needle);
            }));
        }

        return view('livewire.books.index', [
            'books' => $books,
            'company' => $company,
            'currentBook' => $this->book(),
        ]);
    }
}
