<?php

declare(strict_types=1);

namespace App\Livewire\Investments;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Investment\Investment;
use App\Services\Accounting\Exceptions\PostingException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class InvestmentShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public Investment $investment;

    public string $tab = 'valuations';

    public string $actionDate = '';

    public string $fairValue = '';

    public string $incomeAmount = '';

    public string $proceeds = '';

    public function mount(Investment $investment): void
    {
        $company = $this->company();
        if ($company === null || $investment->company_id !== $company->id) {
            abort(404);
        }
        $this->investment = $investment;
        $this->actionDate = now()->toDateString();
    }

    public function revalue(DomainApplicationService $service): void
    {
        $this->perform('fairValue', fn () => $service->revalueInvestment($this->investment, $this->actionDate, $this->fairValue), 'investment.revalued');
    }

    public function recordIncome(DomainApplicationService $service): void
    {
        $this->perform('incomeAmount', fn () => $service->recordInvestmentIncome($this->investment, $this->actionDate, $this->incomeAmount), 'investment.income_recorded');
    }

    public function dispose(DomainApplicationService $service): void
    {
        $this->perform('proceeds', fn () => $service->disposeInvestment($this->investment, $this->actionDate, $this->proceeds), 'investment.disposed_success');
    }

    private function perform(string $field, callable $action, string $message): void
    {
        $this->validate(['actionDate' => 'required|date', $field => 'required|numeric|gt:0']);
        try {
            $action();
            $this->investment->refresh();
            session()->flash('success', __('erp.'.$message));
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $this->investment->loadMissing(['valuations', 'incomes', 'disposals', 'journal']);

        return view('livewire.investments.investment-show', [
            'investment' => $this->investment,
        ]);
    }

    protected function excelTitle(): string
    {
        return __('erp.investment.title').' '.$this->investment->code;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->investment->loadMissing(['valuations', 'incomes', 'disposals']);

        $meta = $this->excelMeta([
            __('erp.code') => $this->investment->code,
            __('erp.investment.name') => $this->investment->name,
            __('erp.investment.classification') => __('erp.investment.classifications.'.$this->investment->classification->value),
            __('erp.investment.cost') => (string) $this->investment->cost,
            __('erp.investment.carrying') => (string) $this->investment->carrying_amount,
            __('erp.status') => $this->statusLabel($this->investment->status),
        ]);

        return [
            $this->excelSheetFrom(
                __('erp.investment.valuations'),
                [
                    [__('erp.date'), ExcelSheet::DATE, fn ($v) => $this->exportDate($v->valued_at)],
                    [__('erp.investment.fair_value'), ExcelSheet::MONEY, fn ($v) => $v->fair_value],
                    [__('erp.document.journal'), ExcelSheet::TEXT, fn ($v) => $v->journal_id],
                ],
                $this->investment->valuations,
                $meta,
                heading: __('erp.investment.title').' '.$this->investment->code,
            ),
            $this->excelSheetFrom(
                __('erp.investment.income'),
                [
                    [__('erp.date'), ExcelSheet::DATE, fn ($i) => $this->exportDate($i->received_at ?? $i->income_date)],
                    [__('erp.amount'), ExcelSheet::MONEY, fn ($i) => $i->amount],
                    [__('erp.document.journal'), ExcelSheet::TEXT, fn ($i) => $i->journal_id],
                ],
                $this->investment->incomes,
                $meta,
            ),
            $this->excelSheetFrom(
                __('erp.investment.disposals'),
                [
                    [__('erp.date'), ExcelSheet::DATE, fn ($d) => $this->exportDate($d->disposed_at)],
                    [__('erp.investment.proceeds'), ExcelSheet::MONEY, fn ($d) => $d->proceeds],
                    [__('erp.document.journal'), ExcelSheet::TEXT, fn ($d) => $d->journal_id],
                ],
                $this->investment->disposals,
                $meta,
            ),
        ];
    }
}
