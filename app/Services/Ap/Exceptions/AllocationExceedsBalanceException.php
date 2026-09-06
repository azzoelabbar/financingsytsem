<?php

declare(strict_types=1);

namespace App\Services\Ap\Exceptions;

class AllocationExceedsBalanceException extends ApException
{
    public static function invoice(string $amount, string $open): self
    {
        return new self("Allocation {$amount} exceeds the invoice open balance {$open}.");
    }

    public static function source(string $amount, string $available): self
    {
        return new self("Allocation {$amount} exceeds the available source amount {$available}.");
    }
}
