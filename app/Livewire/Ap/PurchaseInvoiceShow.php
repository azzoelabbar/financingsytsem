<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class PurchaseInvoiceShow extends Component
{
    use BuildsDocTimeline;
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
}
