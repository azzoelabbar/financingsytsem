<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReceiptIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $receipts = ($company && $book)
            ? app(ArApplicationService::class)->listReceipts(
                $company,
                $book,
                $this->listRequest(searchColumns: 'number'),
            )
            : null;

        return view('livewire.ar.receipt-index', compact('receipts'));
    }
}
