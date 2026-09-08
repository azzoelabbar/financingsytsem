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
class AccountIndex extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company
            ? app(GlApplicationService::class)->listAccounts($company, $this->listRequest(searchColumns: 'code,name_ar'))
            : null;

        return view('livewire.gl.account-index', compact('accounts'));
    }

    protected function excelTitle(): string
    {
        return __('erp.nav.accounts');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();

        if ($company === null) {
            return [];
        }

        $accounts = app(GlApplicationService::class)->listAccounts(
            $company,
            $this->exportRequest(searchColumns: 'code,name_ar'),
        );

        return [$this->excelSheetFrom(
            __('erp.nav.accounts'),
            [
                [__('erp.code'), ExcelSheet::TEXT, fn ($a) => $a->code],
                [__('erp.name'), ExcelSheet::TEXT, fn ($a) => $a->name_ar],
                [__('erp.accounts.type'), ExcelSheet::TEXT, fn ($a) => $a->account_type->labelAr()],
                [__('erp.accounts.posting'), ExcelSheet::TEXT, fn ($a) => $a->is_posting
                    ? __('erp.accounts.postable')
                    : __('erp.accounts.header')],
            ],
            $accounts,
        )];
    }
}
