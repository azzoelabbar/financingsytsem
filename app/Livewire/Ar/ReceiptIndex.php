<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReceiptIndex extends Component
{
    use ExportsToExcel;
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

    protected function excelTitle(): string
    {
        return __('erp.nav.receipts');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $receipts = app(ArApplicationService::class)->listReceipts(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.nav.receipts'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($r) => $r->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.customer.title'), ExcelSheet::TEXT, fn ($r) => $this->localisedName($r->customer)],
                [__('erp.date'), ExcelSheet::DATE, fn ($r) => $this->exportDate($r->receipt_date)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($r) => $r->currency],
                [__('erp.receipt.amount'), ExcelSheet::MONEY, fn ($r) => $r->amount],
                [__('erp.receipt.unallocated'), ExcelSheet::MONEY, fn ($r) => $r->unallocated_amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($r) => $this->statusLabel($r->status)],
            ],
            $receipts,
        )];
    }
}
