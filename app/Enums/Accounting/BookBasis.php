<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

/**
 * Multi-book accounting (spec §40). A single business event may post different
 * treatments into different books.
 */
enum BookBasis: string
{
    case LOCAL = 'local'; // الأساس القانوني المحلي
    case IFRS = 'ifrs';   // أساس التقارير الدولية
    case TAX = 'tax';     // الوعاء الضريبي

    public function labelAr(): string
    {
        return match ($this) {
            self::LOCAL => 'الدفتر المحلي',
            self::IFRS => 'دفتر IFRS',
            self::TAX => 'الدفتر الضريبي',
        };
    }
}
