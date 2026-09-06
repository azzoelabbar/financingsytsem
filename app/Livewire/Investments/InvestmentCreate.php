<?php

declare(strict_types=1);

namespace App\Livewire\Investments;

use App\Application\Api\Other\DomainApplicationService;
use App\Enums\Investment\Classification;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class InvestmentCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $code = '';

    public string $name = '';

    public string $classification = 'FVTPL';

    public string $instrument_type = 'equity';

    public string $date = '';

    public string $currency = 'LYD';

    public string $quantity = '1';

    public string $cost = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('investments', 'code')->where('company_id', $company->id)],
            'name' => 'required|string|max:255', 'classification' => ['required', Rule::enum(Classification::class)],
            'instrument_type' => 'required|string|max:50', 'date' => 'required|date', 'currency' => 'required|string|size:3',
            'quantity' => 'required|numeric|gt:0', 'cost' => 'required|numeric|gt:0',
        ]);
        try {
            $investment = $service->acquireInvestment($company, $book, $this->only(['code', 'name', 'classification', 'instrument_type', 'date', 'currency', 'quantity', 'cost']));
        } catch (PostingException $exception) {
            $this->addError('form', $exception->getMessage());

            return;
        }
        session()->flash('success', __('erp.investment.created'));
        $this->redirectRoute('investments.show', $investment->id, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.investments.investment-create', ['classifications' => Classification::cases()]);
    }
}
