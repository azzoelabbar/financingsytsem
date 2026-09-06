<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\BuildsDocTimeline;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Ar\SalesDebitNote;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Ar\Exceptions\ArException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class DebitNoteShow extends Component
{
    use BuildsDocTimeline;
    use InteractsWithAccountingContext;

    public SalesDebitNote $note;

    public function mount(SalesDebitNote $note): void
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
            app(ArApplicationService::class)->postDebitNote($this->note, $actorId !== null ? (int) $actorId : null);
        } catch (PostingException|ArException $exception) {
            $this->addError('posting', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.debit_note.posted_success'));
        $this->redirectRoute('ar.debit-notes.show', $this->note->id, navigate: false);
    }

    public function render(): View
    {
        $this->note->loadMissing(['customer', 'book', 'journal', 'lines', 'originalInvoice']);

        return view('livewire.ar.note-show', [
            'note' => $this->note,
            'kind' => 'debit',
            'side' => 'ar',
            'dateField' => 'debit_note_date',
            'timeline' => $this->docTimeline($this->note),
        ]);
    }
}
