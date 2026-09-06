<?php

declare(strict_types=1);

namespace App\Services\Accounting\Integrity;

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Enforces the accounting invariants (spec: any difference must surface as an
 * exception). Currently implements the GL-computable invariants (INV-1..3) plus
 * a generic Subledger=Control comparator (INV-5..10) that modules plug into as
 * they are built.
 */
class IntegrityService
{
    public function __construct(
        private readonly TrialBalanceService $trialBalance,
        private readonly FinancialStatementService $statements,
    ) {}

    /**
     * @return array{checks: list<IntegrityCheck>, passed: bool}
     */
    public function check(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $checks = [
            $this->trialBalanceBalanced($company, $book, $asOf),
            $this->accountingEquation($company, $book, $asOf),
            $this->noUnbalancedJournals($company, $book),
        ];

        $passed = ! array_filter($checks, fn (IntegrityCheck $c): bool => $c->failed());

        return ['checks' => $checks, 'passed' => (bool) $passed];
    }

    /** Runs every applicable invariant and throws if any is violated. */
    public function assert(Company $company, AccountingBook $book, ?Carbon $asOf = null): void
    {
        $result = $this->check($company, $book, $asOf);
        $failures = array_values(array_filter($result['checks'], fn (IntegrityCheck $c): bool => $c->failed()));

        if ($failures !== []) {
            throw new IntegrityViolationException($failures);
        }
    }

    /** INV-1: total debits = total credits across the ledger. */
    private function trialBalanceBalanced(Company $company, AccountingBook $book, ?Carbon $asOf): IntegrityCheck
    {
        $totals = $this->trialBalance->totals($company, $book, $asOf);

        return new IntegrityCheck(
            'INV-1', 'Total Debits = Total Credits',
            $totals['balanced'] ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: $totals['debit'],
            actual: $totals['credit'],
            difference: Decimal::sub($totals['debit'], $totals['credit']),
        );
    }

    /** INV-2: Assets = Liabilities + Equity (+ period result). */
    private function accountingEquation(Company $company, AccountingBook $book, ?Carbon $asOf): IntegrityCheck
    {
        $bs = $this->statements->balanceSheet(new ReportRequest(
            companyId: $company->id,
            bookId: $book->id,
            asOf: $asOf?->toDateString(),
        ));

        $assets = $bs['totals']['assets'];
        $rightSide = Decimal::add($bs['totals']['liabilities'], $bs['totals']['equity']);

        return new IntegrityCheck(
            'INV-2', 'Assets = Liabilities + Equity',
            $bs['balanced'] ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: $rightSide,
            actual: $assets,
            difference: Decimal::sub($assets, $rightSide),
        );
    }

    /** INV-3: no stored journal has mismatched debit/credit totals. */
    private function noUnbalancedJournals(Company $company, AccountingBook $book): IntegrityCheck
    {
        $count = DB::table('journals')
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->whereIn('status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->whereColumn('total_debit', '!=', 'total_credit')
            ->count();

        return new IntegrityCheck(
            'INV-3', 'No unbalanced posted journals',
            $count === 0 ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: '0',
            actual: (string) $count,
            difference: (string) $count,
        );
    }

    /**
     * Generic INV-5..10: a control account's GL balance must equal the subledger
     * total (AR/AP/Inventory/Fixed Assets). Modules pass their computed total.
     *
     * @param  numeric-string  $subledgerTotal
     */
    public function controlEqualsSubledger(
        Company $company,
        AccountingBook $book,
        string $controlCode,
        string $subledgerTotal,
        string $invariant = 'INV-5',
        ?Carbon $asOf = null,
    ): IntegrityCheck {
        $glBalance = $this->controlBalanceNatural($company, $book, $controlCode, $asOf);
        $difference = Decimal::sub($glBalance, $subledgerTotal);

        return new IntegrityCheck(
            $invariant, "Subledger = GL Control ({$controlCode})",
            Decimal::equals($glBalance, $subledgerTotal) ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: $glBalance,
            actual: $subledgerTotal,
            difference: $difference,
        );
    }

    /** INV-11: every posting suspense/clearing account must net to zero. */
    public function suspenseCleared(Company $company, AccountingBook $book, ?Carbon $asOf = null): IntegrityCheck
    {
        $rows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $company->id)
            ->where('j.book_id', $book->id)
            ->whereIn('j.status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->where('a.is_suspense', true)
            ->when($asOf, fn ($q) => $q->whereDate('j.posting_date', '<=', $asOf))
            ->groupBy('a.code')
            ->selectRaw('a.code, SUM(jl.functional_debit) as d, SUM(jl.functional_credit) as c')
            ->get();

        $open = [];
        $totalDiff = '0';
        foreach ($rows as $row) {
            $net = Decimal::sub(
                Decimal::of(is_numeric($row->d) ? $row->d : '0'),
                Decimal::of(is_numeric($row->c) ? $row->c : '0'),
            );
            if (! Decimal::equals($net, '0')) {
                $open[] = (string) $row->code;
                $totalDiff = Decimal::add($totalDiff, $net);
            }
        }

        return new IntegrityCheck(
            'INV-11',
            'No unexplained suspense/clearing balance',
            $open === [] ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: '0',
            actual: $totalDiff,
            difference: $totalDiff,
            message: $open === [] ? null : 'Open suspense: '.implode(', ', $open),
        );
    }

    /**
     * Natural (positive-for-normal-side) GL balance of a control account.
     *
     * @return numeric-string
     */
    private function controlBalanceNatural(Company $company, AccountingBook $book, string $code, ?Carbon $asOf): string
    {
        $row = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $company->id)
            ->where('j.book_id', $book->id)
            ->whereIn('j.status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->where('a.code', $code)
            ->when($asOf, fn ($q) => $q->whereDate('j.posting_date', '<=', $asOf))
            ->groupBy('a.normal_balance')
            ->selectRaw('a.normal_balance as nb, SUM(jl.functional_debit) as d, SUM(jl.functional_credit) as c')
            ->first();

        if ($row === null) {
            return '0.000000';
        }

        $debit = Decimal::of(is_numeric($row->d) ? $row->d : '0');
        $credit = Decimal::of(is_numeric($row->c) ? $row->c : '0');

        return $row->nb === 'credit'
            ? Decimal::sub($credit, $debit)
            : Decimal::sub($debit, $credit);
    }
}
