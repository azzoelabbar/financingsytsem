<?php

declare(strict_types=1);

namespace App\Services\Treasury;

use App\Enums\Treasury\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Treasury\CashTransaction;
use App\Models\Treasury\TreasuryAccount;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Book balances and outstanding items for treasury accounts. Book balance =
 * opening + inflows − outflows (functional), and must equal the GL balance of
 * the linked chart account when INV-6 is asserted.
 */
class TreasuryLedgerService
{
    public function __construct(private readonly TrialBalanceService $trialBalance) {}

    /**
     * @return numeric-string
     */
    public function bookBalance(TreasuryAccount $account, ?Carbon $asOf = null): string
    {
        $balance = Decimal::of($account->opening_balance ?? '0');

        foreach ($this->postedTransactions($account, $asOf) as $tx) {
            $functional = Decimal::mul(Decimal::of($tx->amount), Decimal::of((string) $tx->exchange_rate));
            if ($tx->type->value === 'transfer' && (int) $tx->counter_treasury_account_id === (int) $account->id) {
                // Destination side of a transfer (inbound to this account) — stored on the source row.
                // Transfers are stored once on the source account; destination impact is via GL only.
                continue;
            }
            if ($tx->treasury_account_id === $account->id) {
                $balance = $tx->direction === 'in'
                    ? Decimal::add($balance, $functional)
                    : Decimal::sub($balance, $functional);
            }
        }

        // Inbound transfers targeting this account (counter_treasury_account_id).
        foreach ($this->inboundTransfers($account, $asOf) as $tx) {
            $functional = Decimal::mul(Decimal::of($tx->amount), Decimal::of((string) $tx->exchange_rate));
            $balance = Decimal::add($balance, $functional);
        }

        return $balance;
    }

    /**
     * GL natural balance of the linked chart account.
     *
     * @return numeric-string
     */
    public function glBalance(Company $company, AccountingBook $book, TreasuryAccount $account, ?Carbon $asOf = null): string
    {
        $rows = $this->trialBalance->build($company, $book, $asOf);
        foreach ($rows as $row) {
            if ($row->code === $account->gl_account_code) {
                return Decimal::of($row->balance);
            }
        }

        return '0.000000';
    }

    /**
     * Outstanding deposits: inbound posted, uncleared.
     *
     * @return Collection<int, CashTransaction>
     */
    public function outstandingDeposits(TreasuryAccount $account, ?Carbon $asOf = null): Collection
    {
        return $this->postedTransactions($account, $asOf)
            ->filter(fn (CashTransaction $tx): bool => ! $tx->is_cleared && $tx->direction === 'in')
            ->values();
    }

    /**
     * Outstanding cheques/payments: outbound posted, uncleared.
     *
     * @return Collection<int, CashTransaction>
     */
    public function outstandingCheques(TreasuryAccount $account, ?Carbon $asOf = null): Collection
    {
        return $this->postedTransactions($account, $asOf)
            ->filter(fn (CashTransaction $tx): bool => ! $tx->is_cleared && $tx->direction === 'out')
            ->values();
    }

    /**
     * @return numeric-string
     */
    public function outstandingDepositsTotal(TreasuryAccount $account, ?Carbon $asOf = null): string
    {
        $total = '0';
        foreach ($this->outstandingDeposits($account, $asOf) as $tx) {
            $total = Decimal::add($total, Decimal::mul(Decimal::of($tx->amount), Decimal::of((string) $tx->exchange_rate)));
        }

        return $total;
    }

    /**
     * @return numeric-string
     */
    public function outstandingChequesTotal(TreasuryAccount $account, ?Carbon $asOf = null): string
    {
        $total = '0';
        foreach ($this->outstandingCheques($account, $asOf) as $tx) {
            $total = Decimal::add($total, Decimal::mul(Decimal::of($tx->amount), Decimal::of((string) $tx->exchange_rate)));
        }

        return $total;
    }

    /**
     * @return Collection<int, CashTransaction>
     */
    private function postedTransactions(TreasuryAccount $account, ?Carbon $asOf): Collection
    {
        return CashTransaction::query()
            ->where('treasury_account_id', $account->id)
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('transaction_date', '<=', $asOf))
            ->get();
    }

    /**
     * @return Collection<int, CashTransaction>
     */
    private function inboundTransfers(TreasuryAccount $account, ?Carbon $asOf): Collection
    {
        return CashTransaction::query()
            ->where('counter_treasury_account_id', $account->id)
            ->where('type', 'transfer')
            ->where('status', DocumentStatus::POSTED->value)
            ->when($asOf, fn ($q) => $q->whereDate('transaction_date', '<=', $asOf))
            ->get();
    }
}
