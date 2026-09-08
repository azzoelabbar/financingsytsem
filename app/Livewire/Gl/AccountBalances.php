<?php

declare(strict_types=1);

namespace App\Livewire\Gl;

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class AccountBalances extends Component
{
    use ExportsToExcel;
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

    protected function excelTitle(): string
    {
        return __('erp.nav.account_balances');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $rows = app(GlApplicationService::class)->accountBalances($company, $book);

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $rows = array_values(array_filter(
                $rows,
                fn (array $r): bool => str_contains(mb_strtolower($r['code']), $needle)
                    || str_contains(mb_strtolower($r['name_ar']), $needle),
            ));
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($rows as $row) {
            $totalDebit += (float) $row['debit'];
            $totalCredit += (float) $row['credit'];
        }

        return [$this->excelSheetFrom(
            __('erp.nav.account_balances'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn (array $r) => $r['code']],
                [__('erp.name'), ExcelSheet::TEXT, fn (array $r) => $r['name_ar']],
                [__('erp.debit'), ExcelSheet::MONEY, fn (array $r) => $r['debit']],
                [__('erp.credit'), ExcelSheet::MONEY, fn (array $r) => $r['credit']],
                [__('erp.balance'), ExcelSheet::MONEY, fn (array $r) => $r['balance']],
            ],
            $rows,
            $this->excelMeta([
                __('erp.export.filters') => $this->search !== '' ? $this->search : null,
            ]),
            totals: [[__('erp.total'), null, $totalDebit, $totalCredit, null]],
        )];
    }
}
