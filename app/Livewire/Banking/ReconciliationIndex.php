<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\BankReconciliation;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReconciliationIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, BankReconciliation>|null $reconciliations */
        $reconciliations = null;
        if ($company !== null) {
            $reconciliations = BankReconciliation::query()
                ->where('company_id', $company->id)
                ->with('treasuryAccount')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('as_of_date', 'like', $needle)
                        ->orWhereHas('treasuryAccount', fn ($a) => $a->where('code', 'like', $needle)
                            ->orWhere('name_ar', 'like', $needle)
                            ->orWhere('name_en', 'like', $needle)));
                })
                ->orderByDesc('as_of_date')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.reconciliation-index', compact('reconciliations'));
    }

    protected function excelTitle(): string
    {
        return __('erp.banking.reconciliation');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $reconciliations = BankReconciliation::query()
            ->where('company_id', $company->id)
            ->with('treasuryAccount')
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('as_of_date', 'like', $needle)
                    ->orWhereHas('treasuryAccount', fn ($a) => $a->where('code', 'like', $needle)
                        ->orWhere('name_ar', 'like', $needle)
                        ->orWhere('name_en', 'like', $needle)));
            })
            ->orderByDesc('as_of_date')
            ->orderByDesc('id')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('erp.banking.reconciliation'),
            [
                [__('erp.banking.account'), ExcelSheet::TEXT, fn ($r) => $this->localisedName($r->treasuryAccount)],
                [__('erp.date'), ExcelSheet::DATE, fn ($r) => $this->exportDate($r->as_of_date)],
                [__('erp.banking.statement_balance'), ExcelSheet::MONEY, fn ($r) => $r->statement_balance],
                [__('erp.banking.book_balance'), ExcelSheet::MONEY, fn ($r) => $r->book_balance],
                [__('erp.reconciliation.difference'), ExcelSheet::MONEY, fn ($r) => $r->difference],
                [__('erp.status'), ExcelSheet::TEXT, fn ($r) => $this->statusLabel($r->status)],
            ],
            $reconciliations,
            $this->excelMeta([
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
        )];
    }
}
