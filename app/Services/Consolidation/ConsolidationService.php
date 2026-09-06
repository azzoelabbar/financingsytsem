<?php

declare(strict_types=1);

namespace App\Services\Consolidation;

use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\EnginePoster;

class ConsolidationService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    public function eliminateIntercompany(Company $parent, string $date, string $receivable, string $payable, string $amount): Journal
    {
        return $this->poster->post($parent, 'consolidation.eliminate', $date, (string) $parent->functional_currency, 'ELIM', [
            ['account' => $payable, 'debit' => $amount, 'memo' => 'Eliminate IC payable'],
            ['account' => $receivable, 'credit' => $amount, 'memo' => 'Eliminate IC receivable'],
        ]);
    }
}
