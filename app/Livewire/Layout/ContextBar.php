<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ContextBar extends Component
{
    use InteractsWithAccountingContext;

    /** @return Collection<int, Company> */
    public function companiesProperty(): Collection
    {
        $user = auth()->user();
        if ($user === null) {
            return collect();
        }

        return $this->context()->companiesFor($user);
    }

    /** @return Collection<int, AccountingBook> */
    public function booksProperty(): Collection
    {
        $company = $this->company();
        if ($company === null) {
            return collect();
        }

        return AccountingBook::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.layout.context-bar', [
            'companies' => $this->companiesProperty(),
            'books' => $this->booksProperty(),
            'company' => $this->company(),
            'book' => $this->book(),
            'period' => $this->period(),
        ]);
    }
}
