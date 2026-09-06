<?php

declare(strict_types=1);

namespace App\Enums\Treasury;

enum ReconciliationStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
}
