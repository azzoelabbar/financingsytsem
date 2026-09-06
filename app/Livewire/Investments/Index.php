<?php

declare(strict_types=1);

namespace App\Livewire\Investments;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $investments = $company
            ? app(DomainApplicationService::class)->listInvestments($company, $this->listRequest(searchColumns: 'code'))
            : null;

        return view('livewire.investments.index', compact('investments'));
    }
}
