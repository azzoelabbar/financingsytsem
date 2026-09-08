<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\CashTransaction;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TransactionIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        /** @var LengthAwarePaginator<int, CashTransaction>|null $transactions */
        $transactions = null;
        if ($company !== null && $book !== null) {
            $transactions = CashTransaction::query()
                ->where('company_id', $company->id)
                ->where('book_id', $book->id)
                ->with('treasuryAccount')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('number', 'like', $needle)->orWhere('reference', 'like', $needle)->orWhere('description', 'like', $needle));
                })
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.transaction-index', compact('transactions'));
    }

    protected function excelTitle(): string
    {
        return __('erp.banking.transactions');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $transactions = CashTransaction::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->with('treasuryAccount')
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('number', 'like', $needle)->orWhere('reference', 'like', $needle)->orWhere('description', 'like', $needle));
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('erp.banking.transactions'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($t) => $t->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.date'), ExcelSheet::DATE, fn ($t) => $this->exportDate($t->transaction_date)],
                [__('erp.banking.account'), ExcelSheet::TEXT, fn ($t) => $this->localisedName($t->treasuryAccount)],
                [__('erp.banking.tx_type'), ExcelSheet::TEXT, fn ($t) => __('erp.banking.tx_types.'.$t->type->value)],
                [__('erp.document.reference'), ExcelSheet::TEXT, fn ($t) => $t->reference],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($t) => $t->currency],
                [__('erp.amount'), ExcelSheet::MONEY, fn ($t) => $t->direction === 'out'
                    ? -1 * (float) $t->amount
                    : $t->amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($t) => $this->statusLabel($t->status)],
            ],
            $transactions,
            $this->excelMeta([
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
        )];
    }
}
