<?php

declare(strict_types=1);

namespace App\Enums\Treasury;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case REVERSED = 'reversed';

    public function isPosted(): bool
    {
        return $this === self::POSTED;
    }

    public function isMutable(): bool
    {
        return $this === self::DRAFT;
    }
}
