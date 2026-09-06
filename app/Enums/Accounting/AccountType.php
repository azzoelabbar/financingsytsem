<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

/**
 * Top-level account classification, aligned with the supplied Libyan chart of
 * accounts (categories 1-7). This is the canonical "nature" of an account and
 * drives its normal balance, financial-statement placement and closing behaviour.
 *
 * NOTE (design principle, spec §66): the account NAME is never the source of
 * truth. This type — together with the Accounting Rules Engine — determines the
 * accounting treatment.
 */
enum AccountType: string
{
    case ASSET = 'asset';                 // 1 - الأصول
    case LIABILITY = 'liability';         // 2 - الخصوم
    case EQUITY = 'equity';               // 3 - حقوق الملكية
    case REVENUE = 'revenue';             // 4 - الإيرادات
    case COST_OF_SALES = 'cost_of_sales'; // 5 - تكلفة المبيعات
    case EXPENSE = 'expense';             // 6 - المصروفات التشغيلية
    case CLOSING = 'closing';             // 7 - حسابات ختامية
    case MEMO = 'memo';                   // 8 - حسابات نظامية / خارج الميزانية

    /** Category digit used as the first character of every account code. */
    public function categoryCode(): string
    {
        return match ($this) {
            self::ASSET => '1',
            self::LIABILITY => '2',
            self::EQUITY => '3',
            self::REVENUE => '4',
            self::COST_OF_SALES => '5',
            self::EXPENSE => '6',
            self::CLOSING => '7',
            self::MEMO => '8',
        };
    }

    public static function fromCategoryCode(string $code): self
    {
        return match ($code[0] ?? '') {
            '1' => self::ASSET,
            '2' => self::LIABILITY,
            '3' => self::EQUITY,
            '4' => self::REVENUE,
            '5' => self::COST_OF_SALES,
            '6' => self::EXPENSE,
            '7' => self::CLOSING,
            '8' => self::MEMO,
            default => throw new \ValueError("Unknown account category for code: {$code}"),
        };
    }

    /** Default normal balance for the class (individual accounts may override, e.g. contra). */
    public function defaultNormalBalance(): NormalBalance
    {
        return match ($this) {
            self::ASSET, self::COST_OF_SALES, self::EXPENSE => NormalBalance::DEBIT,
            self::LIABILITY, self::EQUITY, self::REVENUE => NormalBalance::CREDIT,
            self::CLOSING, self::MEMO => NormalBalance::NONE,
        };
    }

    public function statement(): StatementType
    {
        return match ($this) {
            self::ASSET, self::LIABILITY, self::EQUITY => StatementType::BALANCE_SHEET,
            self::REVENUE, self::COST_OF_SALES, self::EXPENSE => StatementType::INCOME_STATEMENT,
            self::CLOSING, self::MEMO => StatementType::NONE,
        };
    }

    /** Temporary (nominal) accounts are closed to retained earnings at year end. */
    public function isTemporary(): bool
    {
        return in_array($this, [self::REVENUE, self::COST_OF_SALES, self::EXPENSE], true);
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::ASSET => 'الأصول',
            self::LIABILITY => 'الخصوم',
            self::EQUITY => 'حقوق الملكية',
            self::REVENUE => 'الإيرادات',
            self::COST_OF_SALES => 'تكلفة المبيعات',
            self::EXPENSE => 'المصروفات التشغيلية',
            self::CLOSING => 'حسابات ختامية',
            self::MEMO => 'حسابات نظامية',
        };
    }
}
