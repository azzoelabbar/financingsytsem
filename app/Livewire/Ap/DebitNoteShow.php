<?php

declare(strict_types=1);

namespace App\Livewire\Ap;

use App\Application\Api\Ap\ApApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ap\PurchaseDebitNote;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ap\Exceptions\ApException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DebitNoteShow extends Component
{
    use BuildsDocTimeline;
    use InteractsWithAccountingContext;

    public PurchaseDebitNote $note;

    public function mount(PurchaseDebitNote $note): void
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
            app(ApApplicationService::class)->postDebitNote($this->note, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ApException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.debit_note.posted_success'));
        $this->redirectRoute('ap.debit-notes.show', $this->note->id, navigate: false);
    }

    public function render(): View
    {
        $this->note->loadMissing(['supplier', 'book', 'journal', 'lines', 'originalInvoice']);

        return view('livewire.ap.note-show', [
            'note' => $this->note,
            'kind' => 'debit',
            'side' => 'ap',
            'dateField' => 'debit_note_date',
            'timeline' => $this->docTimeline($this->note),
        ]);
    }
}
