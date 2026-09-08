<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SupplierPaymentIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $payments = ($company && $book)
            ? app(ApApplicationService::class)->listPayments($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.ap.supplier-payment-index', compact('payments'));
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.supplier_payments');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $payments = app(ApApplicationService::class)->listPayments(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.nav.supplier_payments'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($p) => $p->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.supplier.title'), ExcelSheet::TEXT, fn ($p) => $p->supplier?->legal_name],
                [__('erp.date'), ExcelSheet::DATE, fn ($p) => $this->exportDate($p->payment_date)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($p) => $p->currency],
                [__('erp.payment.amount'), ExcelSheet::MONEY, fn ($p) => $p->amount],
                [__('erp.receipt.unallocated'), ExcelSheet::MONEY, fn ($p) => $p->unallocated_amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($p) => $this->statusLabel($p->status)],
            ],
            $payments,
        )];
    }
}
