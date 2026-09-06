<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum RateType: string
{
    case SPOT = 'spot';             // السعر الفوري
    case CLOSING = 'closing';       // سعر الإقفال
    case AVERAGE = 'average';       // المتوسط
    case HISTORICAL = 'historical'; // التاريخي

    public function labelAr(): string
    {
        return match ($this) {
            self::SPOT => 'فوري',
            self::CLOSING => 'إقفال',
            self::AVERAGE => 'متوسط',
            self::HISTORICAL => 'تاريخي',
        };
    }
}
