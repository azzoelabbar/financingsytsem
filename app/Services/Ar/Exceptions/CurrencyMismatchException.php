<?php

declare(strict_types=1);

namespace App\Services\Ar\Exceptions;

/**
 * Allocations require the same document currency. Different rates post realized FX.
 */
class CurrencyMismatchException extends ArException
{
    public static function currency(string $source, string $target): self
    {
        return new self("Cannot allocate {$source} against {$target}; cross-currency cash application is not supported (settle in the invoice currency).");
    }

    public static function rate(string $source, string $target): self
    {
        return new self("Cannot allocate at rate {$source} against document rate {$target}.");
    }
}
