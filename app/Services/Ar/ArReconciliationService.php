<?php

declare(strict_types=1);

namespace App\Services\Ar;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Integrity\IntegrityService;
use Illuminate\Support\Carbon;

/**
 * Reconciles the AR subledger against the GL AR control account (INV-7). The
 * subledger total (from documents/allocations) must equal the control-account
 * GL balance; any difference fails loudly.
 */
class ArReconciliationService
{
    public function __construct(
        private readonly ArLedgerService $ledger,
        private readonly IntegrityService $integrity,
    ) {}

    public function reconcile(Company $company, AccountingBook $book, string $controlCode = '110201', ?Carbon $asOf = null): IntegrityCheck
    {
        $subledgerTotal = $this->ledger->subledgerTotal($company, $asOf, $book);

        return $this->integrity->controlEqualsSubledger($company, $book, $controlCode, $subledgerTotal, 'INV-7', $asOf);
    }

    /** Reconcile and throw if the subledger and control disagree. */
    public function assert(Company $company, AccountingBook $book, string $controlCode = '110201', ?Carbon $asOf = null): IntegrityCheck
    {
        $check = $this->reconcile($company, $book, $controlCode, $asOf);

        if ($check->failed()) {
            throw new IntegrityViolationException([$check]);
        }

        return $check;
    }
}
