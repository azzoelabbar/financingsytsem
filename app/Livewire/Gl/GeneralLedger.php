<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.erp')]
class GeneralLedger extends Component
{
    use InteractsWithAccountingContext;

    #[Url(as: 'account')]
    public string $accountCode = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updatedAccountCode(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        $lines = ($company && $book)
            ? app(GlApplicationService::class)->generalLedger($company, $book, $this->listRequest([
                'account_code' => $this->accountCode !== '' ? $this->accountCode : null,
                'from' => $this->from !== '' ? $this->from : null,
                'to' => $this->to !== '' ? $this->to : null,
            ]))
            : null;

        return view('livewire.gl.general-ledger', compact('lines'));
    }
}
