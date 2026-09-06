<?php

declare(strict_types=1);

namespace App\Enums\Accounting;

enum JournalStatus: string
{
    case DRAFT = 'draft';         // مسودة
    case PENDING = 'pending';     // بانتظار الاعتماد
    case APPROVED = 'approved';   // معتمد (لم يُرحّل بعد)
    case POSTED = 'posted';       // مرحّل
    case REVERSED = 'reversed';   // معكوس
    case VOID = 'void';           // ملغى (قبل الترحيل)

    public function isPosted(): bool
    {
        return $this === self::POSTED;
    }

    /** A posted journal is immutable; it may only be reversed (never deleted/edited). */
    public function isMutable(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING, self::APPROVED], true);
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::PENDING => 'بانتظار الاعتماد',
            self::APPROVED => 'معتمد',
            self::POSTED => 'مرحّل',
            self::REVERSED => 'معكوس',
            self::VOID => 'ملغى',
        };
    }
}
