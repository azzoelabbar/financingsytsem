<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Enums\Ar\DocumentStatus;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PurchaseInvoiceIndex extends Component
{
    use ExportsToExcel;
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

    protected function excelTitle(): string
    {
        return __('erp.purchase_invoice.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $invoices = app(ApApplicationService::class)->listInvoices(
            $company,
            $book,
            $this->exportRequest(
                $this->statusFilter !== '' ? ['status' => $this->statusFilter] : [],
                searchColumns: 'number',
            ),
        );

        return [$this->excelSheetFrom(
            __('erp.purchase_invoice.title'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($i) => $i->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.purchase_invoice.supplier'), ExcelSheet::TEXT, fn ($i) => $i->supplier?->legal_name],
                [__('erp.date'), ExcelSheet::DATE, fn ($i) => $this->exportDate($i->invoice_date)],
                [__('erp.document.due_date'), ExcelSheet::DATE, fn ($i) => $this->exportDate($i->due_date)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($i) => $i->currency],
                [__('erp.total'), ExcelSheet::MONEY, fn ($i) => $i->gross_total],
                [__('erp.document.open_balance'), ExcelSheet::MONEY, fn ($i) => $i->openBalance()],
                [__('erp.status'), ExcelSheet::TEXT, fn ($i) => $this->statusLabel($i->status)],
            ],
            $invoices,
            $this->excelMeta([
                __('erp.status') => $this->statusFilter !== '' ? $this->statusLabel($this->statusFilter) : null,
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
        )];
    }
}
