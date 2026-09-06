<?php

declare(strict_types=1);

namespace App\Services\Ap\Exceptions;

class CurrencyMismatchException extends ApException
{
    public static function currency(string $source, string $target): self
    {
        return new self("Cannot allocate {$source} against {$target}; cross-currency cash application is not supported (settle in the invoice currency).");
    }

    public static function rate(string $source, string $target): self
    {
        return new self("Cannot allocate at rate {$source} against document rate {$target}; FX gain/loss on settlement is Phase E.");
    }
}
