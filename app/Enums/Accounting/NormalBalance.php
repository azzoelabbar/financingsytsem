<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum NormalBalance: string
{
    case DEBIT = 'debit';   // مدين
    case CREDIT = 'credit'; // دائن
    case NONE = 'none';     // — (closing/statistical)

    public function labelAr(): string
    {
        return match ($this) {
            self::DEBIT => 'مدين',
            self::CREDIT => 'دائن',
            self::NONE => '—',
        };
    }

    /** Sign applied to (debit - credit) so a normal balance is positive. */
    public function sign(): int
    {
        return match ($this) {
            self::DEBIT => 1,
            self::CREDIT => -1,
            self::NONE => 1,
        };
    }
}
