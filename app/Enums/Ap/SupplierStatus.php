<?php

declare(strict_types=1);

namespace App\Enums\Ap;

enum SupplierStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED = 'blocked';

    public function canTransact(): bool
    {
        return $this === self::ACTIVE;
    }
}
