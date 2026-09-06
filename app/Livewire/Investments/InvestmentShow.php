<?php

declare(strict_types=1);

namespace App\Livewire\Investments;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Investment\Investment;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class InvestmentShow extends Component
{
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
}
