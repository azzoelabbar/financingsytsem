<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\PeriodStatus;
use App\Enums\Treasury\TreasuryAccountType;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Gl\Accrual;
use App\Models\Gl\PeriodCloseChecklistItem;
use App\Models\Gl\PeriodCloseRun;
use App\Models\Gl\PrepaymentSchedule;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Ap\ApReconciliationService;
use App\Services\Ar\ArReconciliationService;
use App\Services\Assets\AssetReconciliationService;
use App\Services\Gl\Exceptions\PeriodCloseException;
use App\Services\Inventory\InventoryReconciliationService;
use App\Services\Treasury\BankReconciliationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Month-end close checklist: AR/AP/bank recon, integrity, TB, FS, suspense (INV-11),
 * accruals/prepayments hygiene — then soft/hard/lock via PeriodService.
 *
 * INV-9 / INV-10 run when inventory / FA subledgers have activity (empty = pass).
 */
class PeriodCloseService
{
    public function __construct(
        private readonly PeriodService $periods,
        private readonly IntegrityService $integrity,
        private readonly TrialBalanceService $trialBalance,
        private readonly FinancialStatementService $statements,
        private readonly ArReconciliationService $arRecon,
        private readonly ApReconciliationService $apRecon,
        private readonly BankReconciliationService $bankRecon,
        private readonly InventoryReconciliationService $invRecon,
        private readonly AssetReconciliationService $faRecon,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    public function run(
        Company $company,
        AccountingBook $book,
        FiscalPeriod $period,
        string $targetStatus = 'soft_closed',
        ?int $actorId = null,
    ): PeriodCloseRun {
        if (! in_array($targetStatus, ['soft_closed', 'hard_closed', 'locked'], true)) {
            throw new PeriodCloseException("Unsupported target status '{$targetStatus}'.");
        }

        return DB::transaction(function () use ($company, $book, $period, $targetStatus, $actorId): PeriodCloseRun {
            $asOf = Carbon::parse($period->end_date);
            $run = PeriodCloseRun::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'fiscal_period_id' => $period->id,
                'status' => 'in_progress',
                'target_status' => $targetStatus,
                'all_passed' => false,
                'started_by' => $actorId,
                'started_at' => now(),
            ]);

            $this->addItem($run, 'AR_RECON', 'AR subledger = control (INV-7)', $this->checkAr($company, $book, $asOf));
            $this->addItem($run, 'AP_RECON', 'AP subledger = control (INV-8)', $this->checkAp($company, $book, $asOf));
            $this->addItem($run, 'BANK_RECON', 'Bank book = GL (INV-6)', $this->checkBank($company, $book, $asOf));
            $this->addItem($run, 'INTEGRITY', 'GL integrity INV-1..3', $this->checkIntegrity($company, $book, $asOf));
            $this->addItem($run, 'TB', 'Trial balance balanced', $this->checkTb($company, $book, $asOf));
            $this->addItem($run, 'FS', 'Balance sheet balanced (INV-2)', $this->checkFs($company, $book, $asOf));
            $this->addItem($run, 'SUSPENSE', 'No unexplained suspense (INV-11)', $this->checkSuspense($company, $book, $asOf));
            $this->addItem($run, 'ACCRUALS', 'No draft accruals; reversals due processed', $this->checkAccruals($company, $period, $asOf));
            $this->addItem($run, 'PREPAYMENTS', 'No overdue prepayment amortization', $this->checkPrepayments($company, $asOf));
            $this->addItem($run, 'INV_RECON', 'Inventory valuation = GL (INV-9)', $this->checkInventory($company, $book));
            $this->addItem($run, 'FA_RECON', 'Fixed asset register = GL (INV-10)', $this->checkAssets($company, $book));

            $failed = $run->items()->where('status', 'fail')->exists();
            $run->forceFill([
                'all_passed' => ! $failed,
                'status' => $failed ? 'failed' : 'passed',
                'completed_at' => now(),
                'completed_by' => $actorId,
            ])->save();

            $this->audit->record($run, 'checklist_run', $company->id, null, [
                'all_passed' => $run->all_passed,
                'target_status' => $targetStatus,
            ]);

            return $run->load('items');
        });
    }

    public function closeIfPassed(PeriodCloseRun $run, ?int $actorId = null): PeriodCloseRun
    {
        $run->loadMissing('period', 'items');

        if (! $run->all_passed || $run->status === 'failed') {
            throw PeriodCloseException::checklistFailed();
        }

        $target = PeriodStatus::from($run->target_status);
        $period = $run->period;

        match ($target) {
            PeriodStatus::SOFT_CLOSED => $this->periods->softClose($period, $actorId),
            PeriodStatus::HARD_CLOSED => $this->periods->hardClose($period, $actorId),
            PeriodStatus::LOCKED => $this->periods->lock($period, $actorId),
            default => throw new PeriodCloseException("Cannot close to '{$run->target_status}'."),
        };

        $run->forceFill([
            'status' => 'closed',
            'completed_by' => $actorId,
            'completed_at' => now(),
        ])->save();

        $this->audit->record($run, 'period_closed', $run->company_id, null, [
            'fiscal_period_id' => $period->id,
            'status' => $target->value,
        ]);

        return $run->fresh(['items', 'period']) ?? $run;
    }

    /**
     * @param  array{status: string, message?: string, details?: array<string, mixed>}  $result
     */
    private function addItem(PeriodCloseRun $run, string $code, string $name, array $result): PeriodCloseChecklistItem
    {
        return PeriodCloseChecklistItem::create([
            'period_close_run_id' => $run->id,
            'code' => $code,
            'name' => $name,
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'details' => $result['details'] ?? null,
        ]);
    }

    /** @return array{status: string, message?: string, details?: array<string, mixed>} */
    private function checkAr(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        try {
            $check = $this->arRecon->assert($company, $book, $this->controls->arControlCode($company), $asOf);

            return ['status' => 'pass', 'message' => $check->name, 'details' => [
                'expected' => $check->expected,
                'actual' => $check->actual,
            ]];
        } catch (IntegrityViolationException $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    /** @return array{status: string, message?: string, details?: array<string, mixed>} */
    private function checkAp(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        try {
            $check = $this->apRecon->assert($company, $book, null, $asOf);

            return ['status' => 'pass', 'message' => $check->name, 'details' => [
                'expected' => $check->expected,
                'actual' => $check->actual,
            ]];
        } catch (IntegrityViolationException $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    /** @return array{status: string, message?: string, details?: array<string, mixed>} */
    private function checkBank(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        $accounts = TreasuryAccount::query()
            ->where('company_id', $company->id)
            ->where('type', TreasuryAccountType::BANK)
            ->get();

        if ($accounts->isEmpty()) {
            return ['status' => 'pass', 'message' => 'No treasury bank accounts to reconcile'];
        }

        $failures = [];
        foreach ($accounts as $account) {
            try {
                $this->bankRecon->assert($company, $book, $account, $asOf);
            } catch (IntegrityViolationException $e) {
                $failures[] = $account->code.': '.$e->getMessage();
            }
        }

        if ($failures !== []) {
            return ['status' => 'fail', 'message' => implode('; ', $failures)];
        }

        return ['status' => 'pass', 'message' => 'All bank accounts book = GL', 'details' => [
            'accounts' => $accounts->count(),
        ]];
    }

    /** @return array{status: string, message?: string} */
    private function checkIntegrity(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        $result = $this->integrity->check($company, $book, $asOf);

        return $result['passed']
            ? ['status' => 'pass', 'message' => 'INV-1..3 passed']
            : ['status' => 'fail', 'message' => 'Integrity invariants failed'];
    }

    /** @return array{status: string, message?: string, details?: array<string, mixed>} */
    private function checkTb(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        $totals = $this->trialBalance->totals($company, $book, $asOf);

        return $totals['balanced']
            ? ['status' => 'pass', 'message' => 'Trial balance balanced', 'details' => $totals]
            : ['status' => 'fail', 'message' => 'Trial balance out of balance', 'details' => $totals];
    }

    /** @return array{status: string, message?: string} */
    private function checkFs(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        $bs = $this->statements->balanceSheet(new ReportRequest(
            companyId: $company->id,
            bookId: $book->id,
            asOf: $asOf->toDateString(),
        ));

        return $bs['balanced']
            ? ['status' => 'pass', 'message' => 'Balance sheet equation holds']
            : ['status' => 'fail', 'message' => 'Balance sheet does not balance'];
    }

    /** @return array{status: string, message?: string, details?: array<string, mixed>} */
    private function checkSuspense(Company $company, AccountingBook $book, Carbon $asOf): array
    {
        $check = $this->integrity->suspenseCleared($company, $book, $asOf);

        return $check->passed()
            ? ['status' => 'pass', 'message' => $check->name, 'details' => [
                'expected' => $check->expected,
                'actual' => $check->actual,
            ]]
            : ['status' => 'fail', 'message' => $check->message ?? $check->name, 'details' => [
                'expected' => $check->expected,
                'actual' => $check->actual,
                'difference' => $check->difference,
            ]];
    }

    /** @return array{status: string, message?: string} */
    private function checkAccruals(Company $company, FiscalPeriod $period, Carbon $asOf): array
    {
        $drafts = Accrual::query()
            ->where('company_id', $company->id)
            ->where('status', 'draft')
            ->whereDate('accrual_date', '>=', $period->start_date)
            ->whereDate('accrual_date', '<=', $period->end_date)
            ->count();

        $pendingReversals = Accrual::query()
            ->where('company_id', $company->id)
            ->where('status', 'posted')
            ->where('auto_reverse', true)
            ->whereNotNull('reversal_date')
            ->whereDate('reversal_date', '<=', $asOf)
            ->whereNull('reversal_journal_id')
            ->count();

        if ($drafts > 0 || $pendingReversals > 0) {
            return [
                'status' => 'fail',
                'message' => "Draft accruals: {$drafts}; overdue reversals: {$pendingReversals}",
            ];
        }

        return ['status' => 'pass', 'message' => 'Accruals clean'];
    }

    /** @return array{status: string, message?: string} */
    private function checkPrepayments(Company $company, Carbon $asOf): array
    {
        $overdue = PrepaymentSchedule::query()
            ->where('status', 'pending')
            ->whereDate('recognize_date', '<=', $asOf)
            ->whereHas('prepayment', fn ($q) => $q->where('company_id', $company->id)->where('status', 'active'))
            ->count();

        if ($overdue > 0) {
            return ['status' => 'fail', 'message' => "{$overdue} overdue prepayment schedule line(s)"];
        }

        return ['status' => 'pass', 'message' => 'Prepayments current'];
    }

    /** @return array{status: string, message?: string} */
    private function checkInventory(Company $company, AccountingBook $book): array
    {
        try {
            $check = $this->invRecon->assert($company, $book);

            return ['status' => 'pass', 'message' => $check->name];
        } catch (IntegrityViolationException $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    /** @return array{status: string, message?: string} */
    private function checkAssets(Company $company, AccountingBook $book): array
    {
        try {
            $check = $this->faRecon->assert($company, $book);

            return ['status' => 'pass', 'message' => $check->name];
        } catch (IntegrityViolationException $e) {
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }
}
