<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Enums\Ar\DocumentStatus;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PurchaseInvoiceIndex extends Component
{
    use InteractsWithAccountingContext;

    /** Document status to narrow the list to; empty means every status. */
    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $ap = app(ApApplicationService::class);

        $invoices = ($company && $book)
            ? $ap->listInvoices(
                $company,
                $book,
                $this->listRequest(
                    $this->statusFilter !== '' ? ['status' => $this->statusFilter] : [],
                    searchColumns: 'number',
                ),
            )
            : null;

        return view('livewire.ap.purchase-invoice-index', [
            'invoices' => $invoices,
            'company' => $company,
            'book' => $book,
            // Payables position as at today, so the list states how much of it is still open.
            'aging' => $company && $book ? $ap->aging($company, book: $book) : null,
            'statuses' => DocumentStatus::cases(),
        ]);
    }
}
