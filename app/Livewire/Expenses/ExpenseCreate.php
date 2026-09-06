<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ExpenseCreate extends Component
{
    use InteractsWithAccountingContext;

    public string $number = '';

    public string $employee_ref = '';

    public string $claim_date = '';

    public string $currency = 'LYD';

    public string $description = '';

    public string $expense_account = '';

    public string $line_description = '';

    public string $amount = '';

    public function mount(): void
    {
        $this->claim_date = now()->toDateString();
        $company = $this->company();
        $this->currency = $company instanceof Company ? (string) $company->functional_currency : 'LYD';
        $this->number = 'EXP-'.now()->format('Ymd-His');
    }

    public function save(DomainApplicationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate([
            'number' => ['required', 'string', 'max:50', Rule::unique('expense_claims', 'number')->where('company_id', $company->id)],
            'employee_ref' => 'required|string|max:100',
            'claim_date' => 'required|date',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:500',
            'expense_account' => ['required', Rule::exists('accounts', 'code')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_posting', true))],
            'line_description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|gt:0',
        ]);

        try {
            $claim = $service->createExpenseDraft($company, $book, [
                'number' => $this->number, 'employee_ref' => $this->employee_ref,
                'claim_date' => $this->claim_date, 'currency' => strtoupper($this->currency),
                'description' => $this->description ?: null,
            ], [[
                'expense_account' => $this->expense_account,
                'description' => $this->line_description ?: $this->description,
                'amount' => $this->amount,
            ]]);
        } catch (PostingException $exception) {
            $this->addError('form', $exception->getMessage());

            return;
        }

        session()->flash('success', __('erp.expense.created'));
        $this->redirectRoute('expenses.show', $claim->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company ? Account::query()->where('company_id', $company->id)->where('is_posting', true)->where('is_active', true)->orderBy('code')->get() : collect();

        return view('livewire.expenses.expense-create', compact('accounts'));
    }
}
