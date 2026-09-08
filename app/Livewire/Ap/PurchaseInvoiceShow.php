<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PurchaseInvoiceShow extends Component
{
    use BuildsDocTimeline;
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public PurchaseInvoice $invoice;

    public function mount(PurchaseInvoice $invoice): void
    {
        $company = $this->company();
        if ($company === null || $invoice->company_id !== $company->id) {
            abort(404);
        }
        $this->invoice = $invoice;
    }

    public function post(ApApplicationService $ap): void
    {
        abort_unless($this->invoice->status->isMutable(), 409);
        $actorId = auth()->id();
        $actorId = is_int($actorId) ? $actorId : null;
        try {
            $ap->postInvoice($this->invoice, $actorId);
        } catch (PostingException|ApException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }

        session()->flash('success', __('erp.purchase_invoice.posted_success'));
        $this->redirectRoute('ap.invoices.show', $this->invoice->id, navigate: false);
    }

    public function render(): View
    {
        $this->invoice->loadMissing(['supplier', 'book', 'journal', 'lines', 'allocations']);

        return view('livewire.ap.purchase-invoice-show', [
            'invoice' => $this->invoice,
            'timeline' => $this->docTimeline($this->invoice, withAllocations: true),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.purchase_invoice.title').' '.($this->invoice->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->invoice->loadMissing(['supplier', 'lines']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->invoice->number ?? __('erp.sales_invoice.draft_number'),
            __('erp.purchase_invoice.supplier') => $this->invoice->supplier?->legal_name,
            __('erp.date') => $this->exportDate($this->invoice->invoice_date),
            __('erp.document.due_date') => $this->exportDate($this->invoice->due_date),
            __('erp.status') => $this->statusLabel($this->invoice->status),
            __('erp.currency') => $this->invoice->currency,
        ]);

        return [$this->excelSheetFrom(
            __('erp.export.sheet_lines'),
            [
                ['#', ExcelSheet::NUMBER, fn ($l) => $l->line_no],
                [__('erp.sales_invoice.line_description'), ExcelSheet::TEXT, fn ($l) => $l->description],
                [__('erp.sales_invoice.quantity'), ExcelSheet::NUMBER, fn ($l) => $l->quantity],
                [__('erp.sales_invoice.unit_price'), ExcelSheet::MONEY, fn ($l) => $l->unit_price],
                [__('erp.document.tax'), ExcelSheet::MONEY, fn ($l) => $l->tax_amount],
                [__('erp.document.amount'), ExcelSheet::MONEY, fn ($l) => $l->net_amount],
            ],
            $this->invoice->lines,
            $meta,
            totals: [[
                null,
                __('erp.document.total'),
                null,
                $this->invoice->net_total,
                $this->invoice->tax_total,
                $this->invoice->gross_total,
            ]],
            heading: __('erp.purchase_invoice.title').' '.($this->invoice->number ?? ''),
        )];
    }
}
