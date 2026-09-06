<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SalesInvoiceShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public SalesInvoice $invoice;

    public function mount(SalesInvoice $invoice): void
    {
        $company = $this->company();
        if ($company === null || $invoice->company_id !== $company->id) {
            abort(404);
        }
        $this->invoice = $invoice;
    }

    /**
     * Post the draft invoice. Posting is delegated entirely to the AR service
     * (which drives the AccountingEngine) — no accounting logic here.
     */
    public function post(): void
    {
        $this->authorizePost();

        $actorId = auth()->id();
        try {
            app(ArApplicationService::class)->postInvoice($this->invoice, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }

        session()->flash('success', __('erp.sales_invoice.posted_success'));

        $this->redirectRoute('ar.invoices.show', $this->invoice->id, navigate: false);
    }

    private function authorizePost(): void
    {
        // UI guard only — the service/engine remain authoritative on the server.
        abort_unless($this->invoice->status->isMutable(), 409);
    }

    public function render(): View
    {
        $this->invoice->loadMissing(['customer', 'lines', 'journal', 'allocations', 'writeoffs', 'creator', 'poster']);

        return view('livewire.ar.sales-invoice-show', [
            'invoice' => $this->invoice,
            'timeline' => $this->buildTimeline(),
        ]);
    }

    /**
     * Build the document audit timeline from the invoice's own lifecycle fields —
     * real, traceable data (creation, posting), never fabricated.
     *
     * @return list<array<string, mixed>>
     */
    private function buildTimeline(): array
    {
        $events = [];

        $events[] = [
            'label' => __('erp.audit.created'),
            'actor' => $this->invoice->creator?->name,
            'at' => $this->invoice->created_at,
            'tone' => 'default',
        ];

        if ($this->invoice->posted_at !== null) {
            $events[] = [
                'label' => __('erp.audit.posted'),
                'actor' => $this->invoice->poster?->name,
                'at' => $this->invoice->posted_at,
                'tone' => 'success',
            ];
        }

        foreach ($this->invoice->allocations as $allocation) {
            $events[] = [
                'label' => __('erp.audit.allocated'),
                'at' => $allocation->created_at,
                'tone' => 'default',
            ];
        }

        return $events;
    }

    protected function excelTitle(): string
    {
        return __('erp.sales_invoice.title').' '.($this->invoice->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->invoice->loadMissing(['customer', 'lines']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->invoice->number ?? __('erp.sales_invoice.draft_number'),
            __('erp.sales_invoice.customer') => $this->localisedName($this->invoice->customer),
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
            heading: __('erp.sales_invoice.title').' '.($this->invoice->number ?? ''),
        )];
    }
}
