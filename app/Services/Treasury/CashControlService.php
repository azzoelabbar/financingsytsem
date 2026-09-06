<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Enums\Treasury\CashTransactionType;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Treasury\CashCount;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Treasury\Support\TreasuryGuards;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cash counts and over/short adjustments. Differences post through
 * CashTransactionService → AccountingEngine (never direct GL).
 */
class CashControlService
{
    public function __construct(
        private readonly TreasuryLedgerService $ledger,
        private readonly CashTransactionService $transactions,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $header  counted_balance, count_date?, adjustment_account_code?, notes?, post_difference?
     */
    public function recordCount(
        Company $company,
        AccountingBook $book,
        TreasuryAccount $account,
        string|float|int $countedBalance,
        array $header = [],
    ): CashCount {
        if (! $account->isCash()) {
            throw new PostingException('Cash counts apply only to cash treasury accounts.');
        }

        $counted = Decimal::of($countedBalance);
        if (Decimal::isNegative($counted)) {
            throw new PostingException('Counted cash cannot be negative.');
        }

        $system = $this->ledger->bookBalance($account);
        $difference = Decimal::sub($counted, $system);

        return DB::transaction(function () use ($company, $book, $account, $counted, $system, $difference, $header): CashCount {
            $count = CashCount::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'treasury_account_id' => $account->id,
                'count_date' => $header['count_date'] ?? now()->toDateString(),
                'system_balance' => $system,
                'counted_balance' => $counted,
                'difference' => $difference,
                'status' => 'draft',
                'adjustment_account_code' => $header['adjustment_account_code'] ?? '620201',
                'notes' => $header['notes'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);

            $this->audit->record($count, 'created', $company->id, null, [
                'system' => $system,
                'counted' => $counted,
                'difference' => $difference,
            ]);

            if (($header['post_difference'] ?? false) && ! Decimal::equals($difference, '0')) {
                $this->postDifference($count);
            }

            return $count->fresh() ?? $count;
        });
    }

    public function postDifference(CashCount $count): CashCount
    {
        $count->loadMissing('treasuryAccount', 'company', 'book');
        $diff = Decimal::of($count->difference);
        if (Decimal::equals($diff, '0')) {
            $count->forceFill(['status' => 'posted'])->save();

            return $count;
        }

        $account = $count->treasuryAccount;
        $abs = Decimal::isNegative($diff) ? Decimal::sub('0', $diff) : $diff;
        TreasuryGuards::assertPositiveAmount($abs);

        // Counted > system → cash over (receipt). Counted < system → cash short (payment).
        $type = Decimal::isPositive($diff)
            ? CashTransactionType::MISC_RECEIPT
            : CashTransactionType::MISC_PAYMENT;

        $tx = $this->transactions->createDraft($count->company, $count->book, $account, $type, $abs, [
            'transaction_date' => Carbon::parse($count->count_date)->toDateString(),
            'counter_account_code' => $count->adjustment_account_code ?? '620201',
            'description' => 'Cash count adjustment',
            'reference' => 'CC-'.$count->id,
        ]);
        $tx = $this->transactions->post($tx);

        $count->forceFill([
            'status' => 'posted',
            'journal_id' => $tx->journal_id,
        ])->save();

        $this->audit->record($count, 'posted', $count->company_id, null, [
            'journal_id' => $tx->journal_id,
            'difference' => $count->difference,
        ]);

        return $count;
    }
}
