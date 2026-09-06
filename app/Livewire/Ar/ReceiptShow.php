<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\Receipt;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ar\Exceptions\ArException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReceiptShow extends Component
{
    use BuildsDocTimeline;
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public Receipt $receipt;

    public ?int $invoice_id = null;

    public string $allocation_amount = '';

    public function mount(Receipt $receipt): void
    {
        $company = $this->company();
        if ($company === null || $receipt->company_id !== $company->id) {
            abort(404);
        }
        $this->receipt = $receipt;
    }

    public function post(): void
    {
        abort_unless($this->receipt->status->isMutable(), 409);
        $actorId = auth()->id();
        try {
            app(ArApplicationService::class)->postReceipt($this->receipt, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ArException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.receipt.posted_success'));
        $this->redirectRoute('ar.receipts.show', $this->receipt->id, navigate: false);
    }

    public function allocate(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        abort_unless($this->receipt->status->isPosted(), 409);

        $this->validate([
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('sales_invoices', 'id')->where(fn ($query) => $query
                    ->where('company_id', $company->id)
                    ->where('book_id', $book->id)
                    ->where('customer_id', $this->receipt->customer_id)),
            ],
            'allocation_amount' => 'required|numeric|gt:0',
        ]);

        try {
            $invoice = $ar->findInvoice($company, $book, (int) $this->invoice_id);
            $ar->allocateReceipt($this->receipt, $invoice, $this->allocation_amount);
        } catch (PostingException|ArException $exception) {
            $this->addError('allocation', $exception->getMessage());

            return;
        }

        $this->receipt->refresh();
        $this->reset('invoice_id', 'allocation_amount');
        session()->flash('success', __('erp.receipt.allocated_success'));
    }

    public function render(): View
    {
        $this->receipt->loadMissing(['customer', 'book', 'journal', 'allocations.invoice']);
        $openInvoices = SalesInvoice::query()
            ->where('company_id', $this->receipt->company_id)
            ->where('book_id', $this->receipt->book_id)
            ->where('customer_id', $this->receipt->customer_id)
            ->where('status', 'posted')
            ->with('writeoffs')
            ->orderBy('due_date')
            ->get()
            ->filter(fn (SalesInvoice $invoice): bool => (float) $invoice->openBalance() > 0);

        return view('livewire.ar.receipt-show', [
            'receipt' => $this->receipt,
            'openInvoices' => $openInvoices,
            'timeline' => $this->docTimeline($this->receipt, withAllocations: true),
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.receipts').' '.($this->receipt->number ?? '');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->receipt->loadMissing(['customer', 'allocations.invoice']);

        $meta = $this->excelMeta([
            __('erp.number') => $this->receipt->number ?? __('erp.sales_invoice.draft_number'),
            __('erp.customer.title') => $this->localisedName($this->receipt->customer),
            __('erp.date') => $this->exportDate($this->receipt->receipt_date),
            __('erp.status') => $this->statusLabel($this->receipt->status),
            __('erp.currency') => $this->receipt->currency,
            __('erp.receipt.amount') => (string) $this->receipt->amount,
            __('erp.receipt.unallocated') => (string) $this->receipt->unallocated_amount,
        ]);

        return [$this->excelSheetFrom(
            __('erp.export.sheet_allocations'),
            [
                [__('erp.receipt.invoice'), ExcelSheet::TEXT, fn ($a) => $a->invoice?->number],
                [__('erp.date'), ExcelSheet::DATE, fn ($a) => $this->exportDate($a->allocation_date)],
                [__('erp.amount'), ExcelSheet::MONEY, fn ($a) => $a->amount],
            ],
            $this->receipt->allocations,
            $meta,
            heading: __('erp.nav.receipts').' '.($this->receipt->number ?? ''),
        )];
    }
}
