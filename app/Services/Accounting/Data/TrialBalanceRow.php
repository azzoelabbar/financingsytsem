<?php

declare(strict_types=1);

namespace App\Services\Accounting\Data;

/**
 * One line of a trial balance: an account with its aggregated posted debit and
 * credit totals (functional currency) and the resulting net balance.
 */
final readonly class TrialBalanceRow
{
    /**
     * @param  numeric-string  $debit
     * @param  numeric-string  $credit
     * @param  numeric-string  $balance
     */
    public function __construct(
        public int $accountId,
        public string $code,
        public string $nameAr,
        public string $debit,
        public string $credit,
        public string $balance,
    ) {}
}
