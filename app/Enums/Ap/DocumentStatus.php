<?php

declare(strict_types=1);

namespace App\Enums\Ap;

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
}
