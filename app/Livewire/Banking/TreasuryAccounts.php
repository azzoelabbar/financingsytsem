<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\TreasuryAccount;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class TreasuryAccounts extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    /** 'bank' | 'cash' */
    public string $type = 'bank';

    public function mount(string $type = 'bank'): void
    {
        $this->type = in_array($type, ['bank', 'cash'], true) ? $type : 'bank';
    }

    public function render(): View
    {
        $company = $this->company();

        /** @var LengthAwarePaginator<int, TreasuryAccount>|null $accounts */
        $accounts = null;
        if ($company !== null) {
            $accounts = TreasuryAccount::query()
                ->where('company_id', $company->id)
                ->where('type', $this->type)
                ->with('bank')
                ->when($this->search !== '', function ($q): void {
                    $needle = '%'.$this->search.'%';
                    $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name_ar', 'like', $needle)->orWhere('name_en', 'like', $needle));
                })
                ->orderBy('code')
                ->paginate($this->perPage);
        }

        return view('livewire.banking.treasury-accounts', [
            'accounts' => $accounts,
            'type' => $this->type,
        ]);
    }

    protected function excelTitle(): string
    {
        return $this->type === 'cash' ? __('erp.banking.cash_accounts') : __('erp.banking.bank_accounts');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $accounts = TreasuryAccount::query()
            ->where('company_id', $company->id)
            ->where('type', $this->type)
            ->with('bank')
            ->when($this->search !== '', function ($q): void {
                $needle = '%'.$this->search.'%';
                $q->where(fn ($w) => $w->where('code', 'like', $needle)->orWhere('name_ar', 'like', $needle)->orWhere('name_en', 'like', $needle));
            })
            ->orderBy('code')
            ->limit(self::EXPORT_PAGE_SIZE)
            ->get();

        $columns = [
            [__('erp.code'), ExcelSheet::TEXT, fn ($a) => $a->code],
            [__('erp.name'), ExcelSheet::TEXT, fn ($a) => $this->localisedName($a)],
        ];

        if ($this->type === 'bank') {
            $columns[] = [__('erp.banking.bank'), ExcelSheet::TEXT, fn ($a) => $this->localisedName($a->bank)];
            $columns[] = [__('erp.banking.account_number'), ExcelSheet::TEXT, fn ($a) => $a->account_number ?? $a->iban];
        }

        $columns[] = [__('erp.currency'), ExcelSheet::TEXT, fn ($a) => $a->currency];
        $columns[] = [__('erp.banking.opening_balance'), ExcelSheet::MONEY, fn ($a) => $a->opening_balance];
        $columns[] = [__('erp.status'), ExcelSheet::TEXT, fn ($a) => $a->is_active ? __('erp.active') : __('erp.inactive')];

        return [$this->excelSheetFrom($this->excelTitle(), $columns, $accounts)];
    }
}
