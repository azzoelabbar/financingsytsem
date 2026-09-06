<?php

declare(strict_types=1);

namespace App\Services\Ap\Support;

use App\Models\Ap\Supplier;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Ap\Exceptions\InactiveSupplierException;

final class ApGuards
{
    public static function assertSupplierCanTransact(Supplier $supplier): void
    {
        if ($supplier->is_blocked || $supplier->status->value === 'blocked') {
            throw InactiveSupplierException::make($supplier->code, 'blocked');
        }
        if (! $supplier->is_active || $supplier->status->value === 'inactive') {
            throw InactiveSupplierException::make($supplier->code, 'inactive');
        }
    }

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
}
