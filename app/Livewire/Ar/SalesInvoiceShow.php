<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class SalesInvoiceShow extends Component
{
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
}
