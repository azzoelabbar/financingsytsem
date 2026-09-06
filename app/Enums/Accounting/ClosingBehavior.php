<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

/**
 * Separates "how an account behaves at closing" from "which statement it belongs
 * to" (spec §65). Retained earnings and drawings are closing PARTIES yet remain
 * permanent balance-sheet accounts; the P&L/Trading accounts are truly temporary.
 */
enum ClosingBehavior: string
{
    case PERMANENT = 'permanent';           // يرحّل رصيده — أصول/خصوم/حقوق ملكية دائمة
    case TEMPORARY = 'temporary';           // يُقفل إلى الأرباح والخسائر — إيراد/مصروف
    case CLOSING_ACCOUNT = 'closing_account'; // حساب إقفال مؤقت (7101/7102)
    case RETAINED_EARNINGS = 'retained_earnings'; // وعاء استقبال نتيجة السنة
    case OCI = 'oci';                       // يُقفل إلى احتياطيات الدخل الشامل الآخر
    case NONE = 'none';                     // بلا سلوك إقفال (حسابات نظامية/خارج الميزانية)

    public function labelAr(): string
    {
        return match ($this) {
            self::PERMANENT => 'دائم',
            self::TEMPORARY => 'مؤقت (يُقفل)',
            self::CLOSING_ACCOUNT => 'حساب إقفال',
            self::RETAINED_EARNINGS => 'أرباح مرحّلة',
            self::OCI => 'دخل شامل آخر',
            self::NONE => 'بدون',
        };
    }
}
