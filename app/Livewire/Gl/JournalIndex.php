<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class JournalIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $journals = ($company && $book)
            ? app(GlApplicationService::class)->listJournals($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.gl.journal-index', compact('journals'));
    }
}
