<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class BudgetVsActual extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public string $asOf = '';

    public string $budgetPeriod = '';

    public string $budgetAccount = '';

    public string $budgetAmount = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
        $this->budgetPeriod = now()->format('Y-m');
    }

    public function saveBudget(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate([
            'budgetPeriod' => ['required', 'date_format:Y-m'],
            'budgetAccount' => ['required', 'string', Rule::exists('accounts', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_posting', true))],
            'budgetAmount' => ['required', 'numeric', 'gt:0'],
        ]);

        $service->setBudgetLine($company, $book, $this->budgetPeriod, $this->budgetAccount, $this->budgetAmount);
        $this->budgetAmount = '';
        session()->flash('success', __('erp.reports.budget_saved'));
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();

        $report = ($company && $book)
            ? app(DomainApplicationService::class)->budgetVsActual($company, $book, $this->asOf)
            : [];

        $accounts = $company
            ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->orderBy('code')->get(['code', 'name_ar', 'name_en'])
            : collect();

        return view('livewire.reports.budget-vs-actual', [
            'report' => $report,
            'company' => $company,
            'book' => $book,
            'period' => $this->period(),
            'accounts' => $accounts,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.reports.budget_vs_actual');
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $company = $this->company();
        $book = $this->book();

        if ($company === null || $book === null) {
            return [];
        }

        $report = app(DomainApplicationService::class)->budgetVsActual($company, $book, $this->asOf);

        return [$this->excelSheetFrom(
            __('erp.reports.budget_vs_actual'),
            [
                [__('erp.reports.period_key'), ExcelSheet::TEXT, fn (array $r) => $r['period_key'] ?? null],
                [__('erp.code'), ExcelSheet::TEXT, fn (array $r) => $r['account_code'] ?? null],
                [__('erp.project.budget'), ExcelSheet::MONEY, fn (array $r) => $r['budget'] ?? null],
                [__('erp.reports.actual'), ExcelSheet::MONEY, fn (array $r) => $r['actual'] ?? null],
                [__('erp.reports.variance'), ExcelSheet::MONEY, fn (array $r) => $r['variance'] ?? null],
            ],
            $report,
            $this->excelMeta([__('erp.aging.as_of') => $this->asOf]),
        )];
    }
}
