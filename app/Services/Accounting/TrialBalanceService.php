<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Data\TrialBalanceRow;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trial balance and integrity checks (spec §11, §58). Aggregates posted journal
 * lines per account in the functional currency. The grand total of debits must
 * always equal the grand total of credits.
 */
class TrialBalanceService
{
    /**
     * @return Collection<int, TrialBalanceRow>
     */
    public function build(Company $company, AccountingBook $book, ?Carbon $asOf = null): Collection
    {
        $rows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.company_id', $company->id)
            ->where('j.book_id', $book->id)
            // A reversed journal remains in the ledger; its reversing entry offsets
            // it (spec §57 — no deletion). Both are real, historical postings.
            ->whereIn('j.status', [JournalStatus::POSTED->value, JournalStatus::REVERSED->value])
            ->when($asOf, fn ($q) => $q->whereDate('j.posting_date', '<=', $asOf))
            ->groupBy('a.id', 'a.code', 'a.name_ar')
            ->orderBy('a.code')
            ->selectRaw('a.id as account_id, a.code, a.name_ar, '
                .'SUM(jl.functional_debit) as debit, SUM(jl.functional_credit) as credit')
            ->get();

        return $rows->map(function (object $r): TrialBalanceRow {
            $debit = Decimal::of(is_numeric($r->debit) ? $r->debit : '0');
            $credit = Decimal::of(is_numeric($r->credit) ? $r->credit : '0');

            return new TrialBalanceRow(
                accountId: (int) $r->account_id,
                code: (string) $r->code,
                nameAr: (string) $r->name_ar,
                debit: $debit,
                credit: $credit,
                balance: Decimal::sub($debit, $credit),
            );
        });
    }

    /**
     * @return array{debit: numeric-string, credit: numeric-string, balanced: bool}
     */
    public function totals(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $debit = '0';
        $credit = '0';

        foreach ($this->build($company, $book, $asOf) as $row) {
            $debit = Decimal::add($debit, $row->debit);
            $credit = Decimal::add($credit, $row->credit);
        }

        return [
            'debit' => $debit,
            'credit' => $credit,
            'balanced' => Decimal::equals($debit, $credit),
        ];
    }
}
