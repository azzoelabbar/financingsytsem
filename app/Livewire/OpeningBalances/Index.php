<?php

declare(strict_types=1);

namespace App\Livewire\OpeningBalances;

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
        $batches = ($company && $book)
            ? app(DomainApplicationService::class)->listOpeningBalances($company, $book, $this->listRequest())
            : null;

        return view('livewire.opening-balances.index', compact('batches'));
    }
}
