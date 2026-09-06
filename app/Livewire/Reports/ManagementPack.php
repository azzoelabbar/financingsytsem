<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ManagementPack extends Component
{
    use InteractsWithAccountingContext;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $pack = ($company && $book)
            ? app(DomainApplicationService::class)->managementPack($company, $book, $this->asOf)
            : [];

        return view('livewire.reports.management-pack', [
            'pack' => $pack,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }
}
