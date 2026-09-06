<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

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
        $projects = $company
            ? app(DomainApplicationService::class)->listProjects($company, $this->listRequest(searchColumns: 'code,name'))
            : null;

        return view('livewire.projects.index', compact('projects'));
    }
}
