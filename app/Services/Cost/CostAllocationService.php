<?php

declare(strict_types=1);

namespace App\Services\Cost;

use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Services\Accounting\EnginePoster;

class CostAllocationService
{
    public function __construct(
        private readonly EnginePoster $poster,
    ) {}

    public function allocate(Company $company, string $date, string $fromAccount, string $toAccount, string $amount, string $memo = 'Cost allocation'): Journal
    {
        return $this->poster->post($company, 'cost.allocate', $date, (string) $company->functional_currency, 'ALLOC', [
            ['account' => $toAccount, 'debit' => $amount, 'memo' => $memo],
            ['account' => $fromAccount, 'credit' => $amount, 'memo' => $memo],
        ]);
    }
}
