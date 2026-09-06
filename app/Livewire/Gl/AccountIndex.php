<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class AccountIndex extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company
            ? app(GlApplicationService::class)->listAccounts($company, $this->listRequest(searchColumns: 'code,name_ar'))
            : null;

        return view('livewire.gl.account-index', compact('accounts'));
    }
}
