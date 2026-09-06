<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\SalesCreditNote;
use App\Models\Ar\SalesInvoice;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ar\Exceptions\ArException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class CreditNoteShow extends Component
{
    use BuildsDocTimeline;
    use InteractsWithAccountingContext;

    public SalesCreditNote $note;

    public ?int $invoice_id = null;

    public string $allocation_amount = '';

    public function mount(SalesCreditNote $note): void
    {
        $company = $this->company();
        if ($company === null || $note->company_id !== $company->id) {
            abort(404);
        }
        $this->note = $note;
    }

    public function post(): void
    {
        abort_unless($this->note->status->isMutable(), 409);
        $actorId = auth()->id();
        try {
            app(ArApplicationService::class)->postCreditNote($this->note, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ArException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.credit_note.posted_success'));
        $this->redirectRoute('ar.credit-notes.show', $this->note->id, navigate: false);
    }

    public function allocate(ArApplicationService $ar): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        abort_unless($this->note->status->isPosted(), 409);

        $this->validate([
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('sales_invoices', 'id')->where(fn ($query) => $query
                    ->where('company_id', $company->id)
                    ->where('book_id', $book->id)
                    ->where('customer_id', $this->note->customer_id)),
            ],
            'allocation_amount' => 'required|numeric|gt:0',
        ]);

        try {
            $invoice = $ar->findInvoice($company, $book, (int) $this->invoice_id);
            $ar->allocateCreditNote($this->note, $invoice, $this->allocation_amount);
        } catch (PostingException|ArException $exception) {
            $this->addError('allocation', $exception->getMessage());

            return;
        }

        $this->note->refresh();
        $this->reset('invoice_id', 'allocation_amount');
        session()->flash('success', __('erp.note.allocated_success'));
    }

    public function render(): View
    {
        $this->note->loadMissing(['customer', 'book', 'journal', 'lines', 'originalInvoice', 'allocations.invoice']);
        $openInvoices = SalesInvoice::query()
            ->where('company_id', $this->note->company_id)
            ->where('book_id', $this->note->book_id)
            ->where('customer_id', $this->note->customer_id)
            ->where('status', 'posted')
            ->with('writeoffs')
            ->orderBy('due_date')
            ->get()
            ->filter(fn (SalesInvoice $invoice): bool => (float) $invoice->openBalance() > 0);

        return view('livewire.ar.note-show', [
            'note' => $this->note,
            'kind' => 'credit',
            'side' => 'ar',
            'dateField' => 'credit_note_date',
            'postAction' => 'post',
            'openInvoices' => $openInvoices,
            'timeline' => $this->docTimeline($this->note, withAllocations: true),
        ]);
    }
}
