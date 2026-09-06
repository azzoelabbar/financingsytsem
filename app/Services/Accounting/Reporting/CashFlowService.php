<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class CashFlowService
{
    /**
     * Direct cash-flow from GL cash/bank lines, classified by the offset account's
     * cash_flow_classification metadata (operating|investing|financing).
     *
     * @return array{operating: numeric-string, investing: numeric-string, financing: numeric-string, inflows: numeric-string, outflows: numeric-string, net: numeric-string, lines: list<array{journal_id: int, account: string, classification: string, debit: numeric-string, credit: numeric-string}>}
     */
    public function summary(Company $company, AccountingBook $book): array
    {
        $cashRows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $company->id)
            ->where('j.book_id', $book->id)
            ->whereIn('j.status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->where(function ($q): void {
                $q->where('a.is_bank_account', true)->orWhere('a.code', 'like', '1101%');
            })
            ->where('a.is_posting', true)
            ->select('jl.journal_id', 'jl.functional_debit', 'jl.functional_credit', 'a.code')
            ->get();

        $operating = Decimal::of('0');
        $investing = Decimal::of('0');
        $financing = Decimal::of('0');
        $in = Decimal::of('0');
        $out = Decimal::of('0');
        $lines = [];

        foreach ($cashRows as $row) {
            $debit = Decimal::of(is_numeric($row->functional_debit) ? $row->functional_debit : '0');
            $credit = Decimal::of(is_numeric($row->functional_credit) ? $row->functional_credit : '0');
            $in = Decimal::add($in, $debit);
            $out = Decimal::add($out, $credit);
            $class = $this->counterpartClass((int) $row->journal_id, (string) $row->code);
            $net = Decimal::sub($debit, $credit);
            match ($class) {
                'investing' => $investing = Decimal::add($investing, $net),
                'financing' => $financing = Decimal::add($financing, $net),
                default => $operating = Decimal::add($operating, $net),
            };
            $lines[] = [
                'journal_id' => (int) $row->journal_id,
                'account' => (string) $row->code,
                'classification' => $class,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        return [
            'operating' => $operating,
            'investing' => $investing,
            'financing' => $financing,
            'inflows' => $in,
            'outflows' => $out,
            'net' => Decimal::sub($in, $out),
            'lines' => $lines,
        ];
    }

    private function counterpartClass(int $journalId, string $cashCode): string
    {
        $other = DB::table('journal_lines as jl')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('jl.journal_id', $journalId)
            ->where('a.code', '!=', $cashCode)
            ->select('a.cash_flow_classification')
            ->first();
        $class = is_string($other?->cash_flow_classification) ? $other->cash_flow_classification : 'operating';

        return in_array($class, ['operating', 'investing', 'financing'], true) ? $class : 'operating';
    }
}
