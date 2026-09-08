<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Index extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $expenses = ($company && $book)
            ? app(DomainApplicationService::class)->listExpenses($company, $book, $this->listRequest(searchColumns: 'number'))
            : null;

        return view('livewire.expenses.index', compact('expenses'));
    }

    protected function excelTitle(): string
    {
        return __('erp.expense.title');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $expenses = app(DomainApplicationService::class)->listExpenses(
            $company,
            $book,
            $this->exportRequest(searchColumns: 'number'),
        );

        return [$this->excelSheetFrom(
            __('erp.expense.title'),
            [
                [__('erp.number'), ExcelSheet::TEXT, fn ($e) => $e->number ?? __('erp.expense.draft')],
                [__('erp.expense.employee'), ExcelSheet::TEXT, fn ($e) => $e->employee_ref],
                [__('erp.date'), ExcelSheet::DATE, fn ($e) => $this->exportDate($e->claim_date)],
                [__('erp.currency'), ExcelSheet::TEXT, fn ($e) => $e->currency],
                [__('erp.amount'), ExcelSheet::MONEY, fn ($e) => $e->amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($e) => $this->statusLabel($e->status)],
            ],
            $expenses,
        )];
    }
}
