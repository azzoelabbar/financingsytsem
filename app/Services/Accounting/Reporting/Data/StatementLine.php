<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting\Data;

/**
 * One line of a financial statement. Carries the drill-down anchor (accountId +
 * the request filters) so any figure can be traced:
 *   line -> account -> ledger -> journal -> source -> document.
 */
final readonly class StatementLine
{
    /** @param numeric-string $amount */
    public function __construct(
        public int $accountId,
        public string $code,
        public string $nameAr,
        public string $group,   // asset | liability | equity | revenue | expense | ...
        public string $amount,  // natural sign for its section, functional currency
    ) {}
}
