<?php

declare(strict_types=1);

namespace App\Enums\Ar;

/**
 * Lifecycle of an AR document (invoice / credit note / debit note / receipt):
 * Draft → (Pending → Approved) → Posted → Reversed. A posted document is
 * immutable — corrected by credit note / reversal, never edited or deleted.
 */
enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case POSTED = 'posted';
    case REVERSED = 'reversed';

    public function isPosted(): bool
    {
        return $this === self::POSTED;
    }

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
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::POSTED => 'Posted',
            self::REVERSED => 'Reversed',
        };
    }

    /** Locale-aware label for display. */
    public function label(): string
    {
        return app()->getLocale() === 'ar' ? $this->labelAr() : $this->labelEn();
    }

    /** Maps lifecycle state to a design-system badge variant. */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::DRAFT => 'outline',
            self::PENDING => 'warning',
            self::APPROVED => 'primary',
            self::POSTED => 'success',
            self::REVERSED => 'danger',
        };
    }
}
