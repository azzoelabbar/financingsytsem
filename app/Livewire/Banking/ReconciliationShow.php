<?php

declare(strict_types=1);

namespace App\Livewire\Banking;

use App\Enums\Treasury\MatchType;
use App\Livewire\Concerns\ExportsToExcel;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use App\Models\Treasury\BankReconciliation;
use App\Models\Treasury\BankStatementLine;
use App\Models\Treasury\CashTransaction;
use App\Services\Treasury\BankReconciliationService;
use App\Services\Treasury\Exceptions\ReconciliationException;
use App\Support\Export\ExcelSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class ReconciliationShow extends Component
{
    use ExportsToExcel;
    use InteractsWithAccountingContext;

    public BankReconciliation $reconciliation;

    /** @var array<int,int|string|null> */
    public array $match_transaction = [];

    public function mount(BankReconciliation $reconciliation): void
    {
        abort_unless($this->company()?->id === $reconciliation->company_id, 404);
        $this->reconciliation = $reconciliation;
    }

    public function autoMatch(BankReconciliationService $service): void
    {
        try {
            $count = $service->autoMatch($this->reconciliation);
            $service->refresh($this->reconciliation);
        } catch (ReconciliationException $e) {
            $this->addError('reconciliation', $e->getMessage());

            return;
        }session()->flash('success', __('erp.banking.auto_matched', ['count' => $count]));
    }

    public function match(int $lineId, BankReconciliationService $service): void
    {
        $line = BankStatementLine::query()->where('bank_statement_id', $this->reconciliation->bank_statement_id)->findOrFail($lineId);
        $txId = (int) ($this->match_transaction[$lineId] ?? 0);
        $tx = CashTransaction::query()->where('company_id', $this->reconciliation->company_id)->where('treasury_account_id', $this->reconciliation->treasury_account_id)->where('status', 'posted')->where('is_cleared', false)->findOrFail($txId);
        try {
            $service->matchManual($this->reconciliation, $line, $tx, MatchType::MANUAL);
            $service->refresh($this->reconciliation);
        } catch (ReconciliationException $e) {
            $this->addError('reconciliation', $e->getMessage());

            return;
        }
        session()->flash('success', __('erp.banking.matched_success'));
    }

    public function complete(BankReconciliationService $service): void
    {
        $actorId = auth()->id();
        $actorId = is_int($actorId) ? $actorId : null;
        try {
            $service->complete($this->reconciliation, $actorId);
        } catch (ReconciliationException $e) {
            $this->addError('reconciliation', $e->getMessage());

            return;
        }session()->flash('success', __('erp.banking.recon_completed'));
        $this->redirectRoute('banking.reconciliation.show', $this->reconciliation->id, navigate: false);
    }

    public function render(): View
    {
        $this->reconciliation->loadMissing(['treasuryAccount', 'statement.lines.match', 'matches']);
        $transactions = CashTransaction::query()->where('company_id', $this->reconciliation->company_id)->where('treasury_account_id', $this->reconciliation->treasury_account_id)->where('status', 'posted')->where('is_cleared', false)->orderBy('transaction_date')->get();

        return view('livewire.banking.reconciliation-show', ['recon' => $this->reconciliation, 'transactions' => $transactions]);
    }

    protected function excelTitle(): string
    {
        return __('erp.banking.reconciliation').' '.$this->reconciliation->id;
    }

    /** @return list<ExcelSheet> */
    protected function excelSheets(): array
    {
        $this->reconciliation->loadMissing(['treasuryAccount', 'statement.lines']);

        $meta = $this->excelMeta([
            __('erp.banking.account') => $this->localisedName($this->reconciliation->treasuryAccount),
            __('erp.date') => $this->exportDate($this->reconciliation->as_of_date),
            __('erp.banking.statement_balance') => (string) $this->reconciliation->statement_balance,
            __('erp.banking.book_balance') => (string) $this->reconciliation->book_balance,
            __('erp.reconciliation.difference') => (string) $this->reconciliation->difference,
            __('erp.status') => $this->statusLabel($this->reconciliation->status),
        ]);

        $lines = $this->reconciliation->statement->lines;

        return [$this->excelSheetFrom(
            __('erp.banking.statement_lines'),
            [
                [__('erp.date'), ExcelSheet::DATE, fn ($l) => $this->exportDate($l->line_date)],
                [__('erp.document.reference'), ExcelSheet::TEXT, fn ($l) => $l->reference],
                [__('erp.description'), ExcelSheet::TEXT, fn ($l) => $l->description],
                [__('erp.amount'), ExcelSheet::MONEY, fn ($l) => $l->amount],
                [__('erp.status'), ExcelSheet::TEXT, fn ($l) => $l->is_matched
                    ? __('erp.doc_status.reconciled')
                    : __('erp.doc_status.unreconciled')],
            ],
            $lines,
            $meta,
            heading: __('erp.banking.reconciliation').' '.$this->reconciliation->id,
        )];
    }
}
