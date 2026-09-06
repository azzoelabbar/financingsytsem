<?php

declare(strict_types=1);

namespace App\Services\Fx;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Support\Decimal;

/**
 * Realized FX true-up when settling FC documents at a rate different from historical.
 */
class FxSettlementService
{
    public function __construct(
        private readonly AccountingEngine $engine,
    ) {}

    /**
     * @return array{journal: Journal|null, fx_amount: numeric-string, direction: string|null}
     */
    public function postRealized(
        Company $company,
        string $side,
        string $monetaryAccount,
        string $fcAmount,
        string $historicalRate,
        string $settlementRate,
        string $date,
        string $reference,
        ?User $poster = null,
    ): array {
        $delta = Decimal::sub(
            Decimal::mul(Decimal::of($fcAmount), Decimal::of($settlementRate)),
            Decimal::mul(Decimal::of($fcAmount), Decimal::of($historicalRate)),
        );

        if (Decimal::equals($delta, '0')) {
            return ['journal' => null, 'fx_amount' => '0', 'direction' => null];
        }

        $isGain = $side === 'ar'
            ? Decimal::isPositive($delta)
            : Decimal::isNegative($delta);

        $abs = Decimal::isNegative($delta) ? Decimal::sub('0', $delta) : $delta;
        $direction = $isGain ? 'gain' : 'loss';

        $document = new GenericSourceDocument(
            'gl.fx_realized',
            $date,
            (string) $company->functional_currency,
            $reference,
            [
                'monetary_account' => $monetaryAccount,
                'amount' => $abs,
                'direction' => $direction,
                'gain_account' => '420104',
                'loss_account' => '630104',
                'book_basis' => BookBasis::LOCAL->value,
                'exchange_rate' => 1,
            ],
        );

        $journal = $this->engine->postFrom($company, $document, $poster);

        return ['journal' => $journal, 'fx_amount' => $abs, 'direction' => $direction];
    }
}
