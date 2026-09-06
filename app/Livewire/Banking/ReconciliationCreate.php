<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Treasury\BankReconciliationService;
use App\Services\Treasury\Exceptions\ReconciliationException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReconciliationCreate extends Component
{
    use InteractsWithAccountingContext;

    public ?int $treasury_account_id = null;

    public string $statement_number = '';

    public string $statement_date = '';

    public string $period_start = '';

    public string $period_end = '';

    public string $opening_balance = '0';

    public string $closing_balance = '0';

    /** @var array<int,array{line_date:string,reference:string,description:string,amount:string,direction:string}> */
    public array $lines = [];

    public function mount(): void
    {
        $this->statement_date = now()->toDateString();
        $this->period_start = now()->startOfMonth()->toDateString();
        $this->period_end = now()->toDateString();
        $this->lines = [['line_date' => now()->toDateString(), 'reference' => '', 'description' => '', 'amount' => '', 'direction' => 'in']];
    }

    public function addLine(): void
    {
        $this->lines[] = ['line_date' => $this->period_end, 'reference' => '', 'description' => '', 'amount' => '', 'direction' => 'in'];
    }

    public function removeLine(int $i): void
    {
        if (count($this->lines) <= 1) {
            return;
        }unset($this->lines[$i]);
        $this->lines = array_values($this->lines);
    }

    public function save(BankReconciliationService $service): void
    {
        $company = $this->requireCompany();
        $book = $this->requireBook();
        $this->validate(['treasury_account_id' => ['required', 'integer', Rule::exists('treasury_accounts', 'id')->where(fn ($q) => $q->where('company_id', $company->id)->where('type', 'bank')->where('is_active', true))],
            'statement_number' => 'nullable|string|max:100', 'statement_date' => 'required|date', 'period_start' => 'required|date|before_or_equal:period_end', 'period_end' => 'required|date', 'opening_balance' => 'required|numeric', 'closing_balance' => 'required|numeric', 'lines' => 'required|array|min:1',
            'lines.*.line_date' => 'required|date', 'lines.*.reference' => 'nullable|string|max:255', 'lines.*.description' => 'nullable|string|max:500', 'lines.*.amount' => 'required|numeric|gt:0', 'lines.*.direction' => ['required', Rule::in(['in', 'out'])]]);
        $account = TreasuryAccount::query()->where('company_id', $company->id)->findOrFail($this->treasury_account_id);
        try {
            $statement = $service->importStatement($company, $account, ['statement_number' => $this->statement_number ?: null, 'statement_date' => $this->statement_date, 'period_start' => $this->period_start, 'period_end' => $this->period_end, 'opening_balance' => $this->opening_balance, 'closing_balance' => $this->closing_balance, 'currency' => $account->currency, 'source' => 'manual'], array_map(static fn ($l) => ['line_date' => $l['line_date'], 'reference' => $l['reference'] ?: null, 'description' => $l['description'] ?: null, 'amount' => $l['amount'], 'direction' => $l['direction']], $this->lines));
            $recon = $service->start($company, $book, $account, $statement);
            $service->autoMatch($recon);
            $service->refresh($recon);
        } catch (ReconciliationException $e) {
            $this->addError('reconciliation', $e->getMessage());

            return;
        }
        session()->flash('success', __('erp.banking.recon_created'));
        $this->redirectRoute('banking.reconciliation.show', $recon->id, navigate: true);
    }

    public function render(): View
    {
        $company = $this->company();
        $accounts = $company ? TreasuryAccount::query()->where('company_id', $company->id)->where('type', 'bank')->where('is_active', true)->orderBy('code')->get() : collect();

        return view('livewire.banking.reconciliation-create', compact('accounts'));
    }
}
