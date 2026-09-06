<?php

declare(strict_types=1);

namespace App\Enums\Treasury;

enum MatchType: string
{
    case EXACT = 'exact';
    case AMOUNT = 'amount';
    case REFERENCE = 'reference';
    case MANUAL = 'manual';
}
