<?php

declare(strict_types=1);

namespace App\Services\Accounting\Rules;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Contracts\AccountingRule;
use App\Services\Accounting\Contracts\SourceDocument;
use App\Services\Accounting\Data\JournalDraft;
use App\Services\Accounting\Data\LedgerMovement;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

/**
 * Unrealized FX revaluation (IAS 21) — single exposure adjustment.
 * Payload: monetary_account, amount, direction (gain|loss), gain_account, loss_account, book_basis.
 *
 * Gain: Dr monetary · Cr gain
 * Loss: Dr loss · Cr monetary
 * (Same shape for AR assets and AP liabilities once direction is P&L-signed.)
 */
final class FxRevaluationRule implements AccountingRule
{
    public function type(): string
    {
        return 'gl.fx_revaluation';
    }

    public function book(): BookBasis
    {
        return BookBasis::LOCAL;
    }

    public function version(): string
    {
        return '1.0';
    }

    public function build(SourceDocument $document): JournalDraft
    {
        $payload = $document->payload();
        $book = is_string($payload['book_basis'] ?? null) ? $payload['book_basis'] : BookBasis::LOCAL->value;
        $monetary = is_string($payload['monetary_account'] ?? null) ? $payload['monetary_account'] : '';
        $gainAccount = is_string($payload['gain_account'] ?? null) ? $payload['gain_account'] : '420104';
        $lossAccount = is_string($payload['loss_account'] ?? null) ? $payload['loss_account'] : '630105';
        $direction = is_string($payload['direction'] ?? null) ? $payload['direction'] : '';
        if ($monetary === '' || ! in_array($direction, ['gain', 'loss'], true)) {
            throw new PostingException('FX revaluation requires monetary_account and direction gain|loss.');
        }

        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        if (! Decimal::isPositive($amount)) {
            throw new PostingException('FX revaluation amount must be positive.');
        }
        $dims = $document->dimensions();

        $movements = $direction === 'gain'
            ? [
                LedgerMovement::debit($monetary, $amount, $dims, 'Unrealized FX revaluation'),
                LedgerMovement::credit($gainAccount, $amount, $dims, 'Unrealized FX gain'),
            ]
            : [
                LedgerMovement::debit($lossAccount, $amount, $dims, 'Unrealized FX loss'),
                LedgerMovement::credit($monetary, $amount, $dims, 'Unrealized FX revaluation'),
            ];

        return new JournalDraft(
            book: $book,
            source: 'fx_revaluation',
            date: $document->date(),
            reference: $document->reference(),
            description: 'FX revaluation '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
