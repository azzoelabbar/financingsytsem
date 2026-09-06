<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseInvoice;
use App\Models\Ap\SupplierPayment;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PaymentShow extends Component
{
    use BuildsDocTimeline;
    use InteractsWithAccountingContext;

    public SupplierPayment $payment;

    public ?int $invoice_id = null;

    public string $allocation_amount = '';

    public function mount(SupplierPayment $payment): void
    {
        $company = $this->company();
        if ($company === null || $payment->company_id !== $company->id) {
            abort(404);
        }
        $this->payment = $payment;
    }

    public function post(): void
    {
        abort_unless($this->payment->status->isMutable(), 409);
        $actorId = auth()->id();
        try {
            app(ApApplicationService::class)->postPayment($this->payment, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ApException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.payment.posted_success'));
        $this->redirectRoute('ap.payments.show', $this->payment->id, navigate: false);
    }

    public function allocate(ApApplicationService $ap): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        abort_unless($this->payment->status->isPosted(), 409);
        $this->validate([
            'invoice_id' => ['required', 'integer', Rule::exists('purchase_invoices', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('book_id', $book->id)->where('supplier_id', $this->payment->supplier_id))],
            'allocation_amount' => 'required|numeric|gt:0',
        ]);
        try {
            $invoice = $ap->findInvoice($company, $book, (int) $this->invoice_id);
            $ap->allocatePayment($this->payment, $invoice, $this->allocation_amount);
        } catch (PostingException|ApException $exception) {
            $this->addError('allocation', $exception->getMessage());

            return;
        }
        $this->payment->refresh();
        $this->reset('invoice_id', 'allocation_amount');
        session()->flash('success', __('erp.payment.allocated_success'));
    }

    public function render(): View
    {
        $this->payment->loadMissing(['supplier', 'book', 'journal', 'allocations.invoice']);
        $openInvoices = PurchaseInvoice::query()->where('company_id', $this->payment->company_id)->where('book_id', $this->payment->book_id)
            ->where('supplier_id', $this->payment->supplier_id)->where('status', 'posted')->orderBy('due_date')->get()
            ->filter(fn (PurchaseInvoice $invoice): bool => (float) $invoice->openBalance() > 0);

        return view('livewire.ap.payment-show', [
            'payment' => $this->payment,
            'openInvoices' => $openInvoices,
            'timeline' => $this->docTimeline($this->payment, withAllocations: true),
        ]);
    }
}
