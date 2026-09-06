<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum PeriodStatus: string
{
    case OPEN = 'open';               // مفتوحة — يُسمح بالترحيل
    case SOFT_CLOSED = 'soft_closed'; // إقفال مبدئي — ترحيل بصلاحية خاصة فقط
    case HARD_CLOSED = 'hard_closed'; // إقفال نهائي — لا ترحيل إطلاقاً
    case LOCKED = 'locked';           // مقفلة — لا ترحيل ولا إعادة فتح روتينية

    public function allowsPosting(): bool
    {
        return $this === self::OPEN;
    }

    /** Soft close can be posted to only by users holding the override permission. */
    public function allowsPrivilegedPosting(): bool
    {
        return $this === self::OPEN || $this === self::SOFT_CLOSED;
    }

    public function isTerminal(): bool
    {
        return $this === self::HARD_CLOSED || $this === self::LOCKED;
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::OPEN => 'مفتوحة',
            self::SOFT_CLOSED => 'إقفال مبدئي',
            self::HARD_CLOSED => 'إقفال نهائي',
            self::LOCKED => 'مقفلة',
        };
    }
}
