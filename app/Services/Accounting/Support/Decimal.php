<?php

declare(strict_types=1);

namespace App\Services\Accounting\Support;

use InvalidArgumentException;

/**
 * Fixed-scale decimal arithmetic for money (bcmath). Centralised so every
 * amount in the accounting core is computed at the same scale and never as a
 * binary float. All methods take and return numeric strings.
 */
final class Decimal
{
    public const SCALE = 6;

    /**
     * Normalise any scalar amount into a fixed-scale numeric string.
     *
     * @return numeric-string
     */
    public static function of(string|float|int $value): string
    {
        $s = is_string($value) ? trim($value) : (string) $value;

        if ($s === '' || ! is_numeric($s)) {
            throw new InvalidArgumentException("Non-numeric amount: '{$s}'.");
        }

        return bcadd($s, '0', self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    public static function add(string $a, string $b): string
    {
        return bcadd($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    public static function mul(string $a, string $b): string
    {
        return bcmul($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     * @return numeric-string
     */
    public static function div(string $a, string $b): string
    {
        if (bccomp($b, '0', self::SCALE) === 0) {
            throw new InvalidArgumentException('Division by zero.');
        }

        return bcdiv($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     */
    public static function compare(string $a, string $b): int
    {
        return bccomp($a, $b, self::SCALE);
    }

    /**
     * @param  numeric-string  $a
     * @param  numeric-string  $b
     */
    public static function equals(string $a, string $b): bool
    {
        return self::compare($a, $b) === 0;
    }

    /** @param numeric-string $a */
    public static function isPositive(string $a): bool
    {
        return bccomp($a, '0', self::SCALE) > 0;
    }

    /** @param numeric-string $a */
    public static function isNegative(string $a): bool
    {
        return bccomp($a, '0', self::SCALE) < 0;
    }
}
