<?php

declare(strict_types=1);

namespace App\Services\Treasury\Support;

use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

final class TreasuryGuards
{
    public static function assertCurrency(string $currency): void
    {
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new PostingException("Invalid currency '{$currency}'; expected an ISO 4217 code.");
        }
    }

    public static function assertExchangeRate(string|float|int $rate): string
    {
        $normalized = Decimal::of($rate);
        if (! Decimal::isPositive($normalized)) {
            throw new PostingException('Exchange rate must be a positive number.');
        }

        return $normalized;
    }

    public static function assertPositiveAmount(string|float|int $amount): string
    {
        $normalized = Decimal::of($amount);
        if (! Decimal::isPositive($normalized)) {
            throw new PostingException('Amount must be positive.');
        }

        return $normalized;
    }
}
