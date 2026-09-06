<?php

declare(strict_types=1);

namespace App\Services\Accounting\Data;

use App\Services\Accounting\Support\Decimal;

/**
 * An immutable single ledger movement expressed by a rule: an account (by code)
 * with a debit XOR credit in the transaction currency, plus optional analytical
 * dimensions. The engine resolves the code to an account id and posts it.
 */
final readonly class LedgerMovement
{
    /** @param array<string, string> $dimensions dimension code => value code */
    public function __construct(
        public string $accountCode,
        public string $debit,
        public string $credit,
        public array $dimensions = [],
        public ?string $memo = null,
    ) {}

    /** @param array<string, string> $dimensions */
    public static function debit(string $accountCode, string|float|int $amount, array $dimensions = [], ?string $memo = null): self
    {
        return new self($accountCode, Decimal::of($amount), '0', $dimensions, $memo);
    }

    /** @param array<string, string> $dimensions */
    public static function credit(string $accountCode, string|float|int $amount, array $dimensions = [], ?string $memo = null): self
    {
        return new self($accountCode, '0', Decimal::of($amount), $dimensions, $memo);
    }
}
