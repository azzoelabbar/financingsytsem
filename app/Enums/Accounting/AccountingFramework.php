<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

/**
 * Reporting framework selected per company (spec §6). The system must NOT claim
 * IFRS compliance unless the relevant requirements are met.
 */
enum AccountingFramework: string
{
    case FULL_IFRS = 'full_ifrs';
    case IFRS_FOR_SMES = 'ifrs_for_smes';
    case LOCAL_GAAP = 'local_gaap';

    public function labelAr(): string
    {
        return match ($this) {
            self::FULL_IFRS => 'معايير IFRS الكاملة',
            self::IFRS_FOR_SMES => 'IFRS للمنشآت الصغيرة والمتوسطة',
            self::LOCAL_GAAP => 'الإطار المحاسبي المحلي',
        };
    }
}
