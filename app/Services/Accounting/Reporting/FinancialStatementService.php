<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Enums\Accounting\JournalStatus;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\Data\StatementLine;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the Statement of Financial Position and the Profit or Loss from posted
 * GL, using the account metadata (statement / account_type) as the mapping. It
 * reads only — the ledger is the source of truth. Contra accounts net naturally
 * because the raw (debit − credit) is summed within each account type.
 *
 * Every line carries its accountId as the drill-down anchor
 * (line → account → ledger → journal → source → document).
 */
class FinancialStatementService
{
    /**
     * @return array{lines: list<StatementLine>, totals: array{assets: numeric-string, liabilities: numeric-string, equity: numeric-string, net_result: numeric-string}, balanced: bool}
     */
    public function balanceSheet(ReportRequest $request): array
    {
        $lines = [];
        $assets = '0';
        $liabilities = '0';
        $equity = '0';

        foreach ($this->aggregate($request, 'balance_sheet') as $row) {
            $debit = Decimal::of(is_numeric($row->d) ? $row->d : '0');
            $credit = Decimal::of(is_numeric($row->c) ? $row->c : '0');

            [$group, $amount] = match ((string) $row->account_type) {
                'asset' => ['asset', Decimal::sub($debit, $credit)],
                'liability' => ['liability', Decimal::sub($credit, $debit)],
                default => ['equity', Decimal::sub($credit, $debit)],
            };

            if (! $request->includeZero && Decimal::equals($amount, '0')) {
                continue;
            }

            $lines[] = new StatementLine((int) $row->id, (string) $row->code, (string) $row->name_ar, $group, $amount);

            $assets = $group === 'asset' ? Decimal::add($assets, $amount) : $assets;
            $liabilities = $group === 'liability' ? Decimal::add($liabilities, $amount) : $liabilities;
            $equity = $group === 'equity' ? Decimal::add($equity, $amount) : $equity;
        }

        $netResult = $this->netResult($request);
        $equityWithResult = Decimal::add($equity, $netResult);
        $rightSide = Decimal::add($liabilities, $equityWithResult);

        return [
            'lines' => $lines,
            'totals' => [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equityWithResult,
                'net_result' => $netResult,
            ],
            'balanced' => Decimal::equals($assets, $rightSide),
        ];
    }

    /**
     * @return array{lines: list<StatementLine>, revenue: numeric-string, expenses: numeric-string, net_profit: numeric-string}
     */
    public function incomeStatement(ReportRequest $request): array
    {
        $lines = [];
        $revenue = '0';
        $expenses = '0';

        foreach ($this->aggregate($request, 'income_statement') as $row) {
            $debit = Decimal::of(is_numeric($row->d) ? $row->d : '0');
            $credit = Decimal::of(is_numeric($row->c) ? $row->c : '0');
            $type = (string) $row->account_type;

            if ($type === 'revenue') {
                $amount = Decimal::sub($credit, $debit);
                $revenue = Decimal::add($revenue, $amount);
                $group = 'revenue';
            } else { // cost_of_sales | expense
                $amount = Decimal::sub($debit, $credit);
                $expenses = Decimal::add($expenses, $amount);
                $group = $type;
            }

            if (! $request->includeZero && Decimal::equals($amount, '0')) {
                continue;
            }

            $lines[] = new StatementLine((int) $row->id, (string) $row->code, (string) $row->name_ar, $group, $amount);
        }

        return [
            'lines' => $lines,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_profit' => Decimal::sub($revenue, $expenses),
        ];
    }

    /**
     * Net result for the period = revenue − expenses (income-statement accounts).
     *
     * @return numeric-string
     */
    private function netResult(ReportRequest $request): string
    {
        $net = '0';

        foreach ($this->aggregate($request, 'income_statement') as $row) {
            $debit = Decimal::of(is_numeric($row->d) ? $row->d : '0');
            $credit = Decimal::of(is_numeric($row->c) ? $row->c : '0');
            $net = Decimal::add($net, Decimal::sub($credit, $debit)); // revenue − expense
        }

        return $net;
    }

    /**
     * Aggregate posted (and reversed — their mirrors offset) journal lines per
     * account for the given statement class.
     *
     * @return Collection<int, \stdClass>
     */
    private function aggregate(ReportRequest $request, string $statement): Collection
    {
        return DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $request->companyId)
            ->where('j.book_id', $request->bookId)
            ->whereIn('j.status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->where('a.statement', $statement)
            ->when($request->asOf, fn ($q) => $q->whereDate('j.posting_date', '<=', $request->asOf))
            ->groupBy('a.id', 'a.code', 'a.name_ar', 'a.account_type')
            ->orderBy('a.code')
            ->selectRaw('a.id as id, a.code as code, a.name_ar as name_ar, a.account_type as account_type, '
                .'SUM(jl.functional_debit) as d, SUM(jl.functional_credit) as c')
            ->get();
    }
}
