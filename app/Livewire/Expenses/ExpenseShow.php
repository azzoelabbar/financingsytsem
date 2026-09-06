<?php

declare(strict_types=1);

namespace App\Livewire\Expenses;

use App\Application\Api\Other\DomainApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Expense\ExpenseClaim;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Expense\ExpenseReimbursementService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ExpenseShow extends Component
{
    use InteractsWithAccountingContext;

    public ExpenseClaim $claim;

    public string $rejectionReason = '';

    public string $reimbursementDate = '';

    public string $reimbursementAmount = '';

    public function mount(ExpenseClaim $claim): void
    {
        $company = $this->company();
        if ($company === null || $claim->company_id !== $company->id) {
            abort(404);
        }
        $this->claim = $claim;
        $this->reimbursementDate = now()->toDateString();
    }

    public function submit(): void
    {
        $this->run(fn ($s) => $s->submitExpense($this->claim), 'expense.submitted');
    }

    public function approve(): void
    {
        $this->run(fn ($s) => $s->approveExpense($this->claim), 'expense.approved');
    }

    public function reject(): void
    {
        $this->validate(['rejectionReason' => 'required|string|max:500']);
        $this->run(fn ($s) => $s->rejectExpense($this->claim, $this->rejectionReason), 'expense.rejected');
    }

    public function post(): void
    {
        $this->run(fn ($s) => $s->postExpense($this->claim), 'expense.posted');
    }

    public function reimburse(): void
    {
        $this->validate(['reimbursementDate' => 'required|date', 'reimbursementAmount' => 'nullable|numeric|gt:0']);
        $date = $this->reimbursementDate;
        $amount = $this->reimbursementAmount !== '' ? $this->reimbursementAmount : null;
        $this->run(fn ($s) => app(ExpenseReimbursementService::class)->reimburse($this->claim, $date, $amount), 'expense.reimbursement_posted');
    }

    private function run(callable $action, string $messageKey): void
    {
        try {
            $action(app(DomainApplicationService::class));
            session()->flash('success', __('erp.'.$messageKey));
            $this->redirectRoute('expenses.show', $this->claim->id, navigate: false);
        } catch (PostingException $exception) {
            $this->addError('action', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $this->claim->loadMissing(['lines', 'journal', 'approvals', 'reimbursements']);

        return view('livewire.expenses.expense-show', [
            'claim' => $this->claim,
            'status' => (string) $this->claim->status,
        ]);
    }
}
