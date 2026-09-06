<?php

declare(strict_types=1);

namespace App\Services\Accounting\Data;

/**
 * Immutable input for a single journal line. Amounts are in the line's
 * transaction currency; the service derives the functional-currency amounts.
 *
 * @param  array<int, int>  $dimensions  map of dimension_id => dimension_value_id
 */
final readonly class LineInput
{
    /** @param array<int, int> $dimensions map of dimension_id => dimension_value_id */
    public function __construct(
        public int $accountId,
        public string|float|int $debit = 0,
        public string|float|int $credit = 0,
        public ?string $description = null,
        public ?string $currency = null,
        public ?float $exchangeRate = null,
        public array $dimensions = [],
    ) {}

    /** @param array<int, int> $dimensions */
    public static function debit(int $accountId, string|float|int $amount, ?string $description = null, array $dimensions = []): self
    {
        return new self($accountId, debit: $amount, description: $description, dimensions: $dimensions);
    }

    /** @param array<int, int> $dimensions */
    public static function credit(int $accountId, string|float|int $amount, ?string $description = null, array $dimensions = []): self
    {
        return new self($accountId, credit: $amount, description: $description, dimensions: $dimensions);
    }
}
