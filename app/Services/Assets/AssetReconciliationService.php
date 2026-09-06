<?php

declare(strict_types=1);

namespace App\Services\Assets;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Support\Decimal;
use App\Services\Accounting\TrialBalanceService;

class AssetReconciliationService
{
    public function __construct(
        private readonly AssetService $assets,
        private readonly TrialBalanceService $trialBalance,
    ) {}

    public function assert(Company $company, AccountingBook $book, string $costCode = '120104', string $accumCode = '120105'): IntegrityCheck
    {
        $register = $this->assets->registerTotal($company);
        $rows = $this->trialBalance->build($company, $book)->keyBy('code');
        $cost = $this->debitNet($rows->get($costCode));
        $accum = $this->creditNet($rows->get($accumCode));
        $impair = $this->creditNet($rows->get('120115'));
        $gl = Decimal::sub(Decimal::sub($cost, $accum), $impair);

        $check = new IntegrityCheck(
            'INV-10',
            "Fixed Asset Register = FA GL ({$costCode})",
            Decimal::equals($register, $gl) ? IntegrityCheck::PASS : IntegrityCheck::FAIL,
            expected: $gl,
            actual: $register,
            difference: Decimal::sub($gl, $register),
        );
        if ($check->failed()) {
            throw new IntegrityViolationException([$check]);
        }

        return $check;
    }

    /** @return numeric-string */
    private function debitNet(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->debit), Decimal::of($row->credit));
    }

    /** @return numeric-string */
    private function creditNet(mixed $row): string
    {
        if ($row === null) {
            return Decimal::of('0');
        }

        return Decimal::sub(Decimal::of($row->credit), Decimal::of($row->debit));
    }
}
