<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Integrity\IntegrityService;

class InventoryReconciliationService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly IntegrityService $integrity,
    ) {}

    public function assert(Company $company, AccountingBook $book, string $controlCode = '110301'): IntegrityCheck
    {
        $check = $this->integrity->controlEqualsSubledger(
            $company,
            $book,
            $controlCode,
            $this->inventory->valuationTotal($company),
            'INV-9',
        );
        if ($check->failed()) {
            throw new IntegrityViolationException([$check]);
        }

        return $check;
    }
}
