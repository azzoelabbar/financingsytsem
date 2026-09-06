<?php

declare(strict_types=1);

namespace App\Enums\Treasury;

enum TreasuryAccountType: string
{
    case CASH = 'cash';
    case BANK = 'bank';
}
