<?php

declare(strict_types=1);

namespace App\Services\Ap;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Integrity\IntegrityService;
use Illuminate\Support\Carbon;

/**
 * Reconciles the AP subledger against the GL AP control (INV-8). The control
 * account code is resolved from the chart, never hard-coded.
 */
class ApReconciliationService
{
    public function __construct(
        private readonly ApLedgerService $ledger,
        private readonly IntegrityService $integrity,
        private readonly ControlAccountResolver $controls,
    ) {}

    public function reconcile(Company $company, AccountingBook $book, ?string $controlCode = null, ?Carbon $asOf = null): IntegrityCheck
    {
        $code = $controlCode ?? $this->controls->apControlCode($company);
        $subledgerTotal = $this->ledger->subledgerTotal($company, $asOf, book: $book);

        return $this->integrity->controlEqualsSubledger($company, $book, $code, $subledgerTotal, 'INV-8', $asOf);
    }

    public function assert(Company $company, AccountingBook $book, ?string $controlCode = null, ?Carbon $asOf = null): IntegrityCheck
    {
        $check = $this->reconcile($company, $book, $controlCode, $asOf);

        if ($check->failed()) {
            throw new IntegrityViolationException([$check]);
        }

        return $check;
    }
}
