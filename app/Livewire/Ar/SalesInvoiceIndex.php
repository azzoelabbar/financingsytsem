<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Enums\Ar\DocumentStatus;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SalesInvoiceIndex extends Component
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
        $ar = app(ArApplicationService::class);

        $invoices = ($company && $book)
            ? $ar->listInvoices(
                $company,
                $book,
                $this->listRequest(
                    $this->statusFilter !== '' ? ['status' => $this->statusFilter] : [],
                    searchColumns: 'number',
                ),
            )
            : null;

        return view('livewire.ar.sales-invoice-index', [
            'invoices' => $invoices,
            'company' => $company,
            'book' => $book,
            // Receivables position as at today, so the list states how much of it is still open.
            'aging' => $company && $book ? $ar->aging($company, book: $book) : null,
            'statuses' => DocumentStatus::cases(),
        ]);
    }
}
