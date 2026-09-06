<?php

declare(strict_types=1);

namespace App\Services\Ap\Exceptions;

class InactiveSupplierException extends ApException
{
    public static function make(string $code, string $reason): self
    {
        return new self("Supplier {$code} cannot transact: {$reason}.");
    }
}
