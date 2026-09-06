<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum StatementType: string
{
    case BALANCE_SHEET = 'balance_sheet';       // ميزانية عمومية / SFP
    case INCOME_STATEMENT = 'income_statement'; // قائمة الدخل / P&L
    case NONE = 'none';                         // ختامي — لا يظهر في القوائم

    public function labelAr(): string
    {
        return match ($this) {
            self::BALANCE_SHEET => 'قائمة المركز المالي',
            self::INCOME_STATEMENT => 'قائمة الدخل',
            self::NONE => 'حساب ختامي',
        };
    }
}
