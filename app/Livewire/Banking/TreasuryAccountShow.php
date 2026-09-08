<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\CashTransaction;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Treasury\TreasuryLedgerService;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TreasuryAccountShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public TreasuryAccount $account;

    public function mount(TreasuryAccount $account): void
    {
        abort_unless($this->company()?->id === $account->company_id, 404);
        $this->account = $account;
    }

    public function render(TreasuryLedgerService $ledger): View
    {
        $this->account->loadMissing(['bank', 'transactions' => fn ($q) => $q->latest('transaction_date')->limit(20)]);

        return view('livewire.banking.treasury-account-show', ['account' => $this->account, 'bookBalance' => $ledger->bookBalance($this->account)]);
    }

    protected function excelTitle(): string
    {
        return __('erp.banking.account_details').' '.$this->account->code;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->account->loadMissing('bank');

        $meta = $this->excelMeta([
            __('erp.code') => $this->account->code,
            __('erp.name') => $this->localisedName($this->account),
            __('erp.banking.bank') => $this->localisedName($this->account->bank),
            __('erp.currency') => $this->account->currency,
            __('erp.banking.book_balance') => app(TreasuryLedgerService::class)->bookBalance($this->account),
        ]);

        $transactions = CashTransaction::query()
            ->where('treasury_account_id', $this->account->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        return [$this->excelSheetFrom(
            __('erp.banking.transactions'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($t) => $t->number ?? __('erp.sales_invoice.draft_number')],
                [__('erp.date'), ExcelSheet::DATE, fn ($t) => $this->exportDate($t->transaction_date)],
                [__('erp.banking.tx_type'), ExcelSheet::TEXT, fn ($t) => __('erp.banking.tx_types.'.$t->type->value)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($t) => $t->currency],
                [__('erp.amount'), ExcelSheet::MONEY, fn ($t) => $t->amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($t) => $this->statusLabel($t->status)],
            ],
            $transactions,
            $meta,
            heading: __('erp.banking.account_details').' '.$this->account->code,
        )];
    }
}
