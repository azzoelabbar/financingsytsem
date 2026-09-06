<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class BalanceSheet extends Component
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
        $report = ($company && $book)
            ? app(GlApplicationService::class)->balanceSheet($company, $book, $this->asOf)
            : ['lines' => [], 'totals' => [], 'balanced' => true];

        return view('livewire.reports.balance-sheet', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
        ]);
    }
}
