<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Reconciliation extends Component
{
    use ExportsToExcel;
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
        $result = ($company && $book)
            ? app(ArApplicationService::class)->reconcile($company, $book, Carbon::parse($this->asOf))
            : null;

        return view('livewire.ar.reconciliation', compact('result'));
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.ar_reconciliation');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $result = app(ArApplicationService::class)->reconcile($company, $book, Carbon::parse($this->asOf));
        $gl = (string) ($result['expected'] ?? '0');
        $subledger = (string) ($result['actual'] ?? '0');

        return [$this->excelKeyValueSheet(
            __('erp.nav.ar_reconciliation'),
            [
                __('erp.reconciliation.gl_balance_ar') => $gl,
                __('erp.reconciliation.subledger_ar') => $subledger,
                __('erp.reconciliation.difference_amount') => (float) $gl - (float) $subledger,
            ],
            meta: $this->excelMeta([
                __('erp.aging.as_of') => $this->asOf,
                __('erp.status') => ($result['passed'] ?? false)
                    ? __('erp.reconciliation.matched')
                    : __('erp.reconciliation.difference'),
            ]),
        )];
    }
}
