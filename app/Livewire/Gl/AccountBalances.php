<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class AccountBalances extends Component
{
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        $rows = ($company && $book)
            ? app(GlApplicationService::class)->accountBalances($company, $book)
            : [];

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $rows = array_values(array_filter(
                $rows,
                fn (array $r): bool => str_contains(mb_strtolower($r['code']), $needle)
                    || str_contains(mb_strtolower($r['name_ar']), $needle),
            ));
        }

        return view('livewire.gl.account-balances', [
            'rows' => $rows,
            'company' => $company,
            'book' => $book,
        ]);
    }
}
