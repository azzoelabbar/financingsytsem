<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Journal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class JournalShow extends Component
{
    use InteractsWithAccountingContext;

    public Journal $journal;

    public function mount(Journal $journal): void
    {
        $company = $this->company();
        $book = $this->book();
        if ($company === null || $book === null
            || $journal->company_id !== $company->id
            || $journal->book_id !== $book->id) {
            abort(404);
        }
        $this->journal = app(GlApplicationService::class)->findJournal($company, $book, $journal->id);
    }

    public function render(): View
    {
        return view('livewire.gl.journal-show');
    }
}
