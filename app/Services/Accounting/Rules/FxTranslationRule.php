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
 * Foreign-currency translation residual → OCI reserve (IAS 21).
 * Payload: amount, direction (credit_oci|debit_oci), oci_account, contra_account, book_basis.
 */
final class FxTranslationRule implements AccountingRule
{
    public function type(): string
    {
        return 'gl.fx_translation';
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
        $oci = is_string($payload['oci_account'] ?? null) ? $payload['oci_account'] : '330102';
        $contra = is_string($payload['contra_account'] ?? null) ? $payload['contra_account'] : '320202';
        $direction = is_string($payload['direction'] ?? null) ? $payload['direction'] : '';
        if (! in_array($direction, ['credit_oci', 'debit_oci'], true)) {
            throw new PostingException('FX translation requires direction credit_oci|debit_oci.');
        }

        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        if (! Decimal::isPositive($amount)) {
            throw new PostingException('FX translation amount must be positive.');
        }
        $dims = $document->dimensions();

        $movements = $direction === 'credit_oci'
            ? [
                LedgerMovement::debit($contra, $amount, $dims, 'Translation residual'),
                LedgerMovement::credit($oci, $amount, $dims, 'CTA / OCI'),
            ]
            : [
                LedgerMovement::debit($oci, $amount, $dims, 'CTA / OCI'),
                LedgerMovement::credit($contra, $amount, $dims, 'Translation residual'),
            ];

        return new JournalDraft(
            book: $book,
            source: 'fx_translation',
            date: $document->date(),
            reference: $document->reference(),
            description: 'FX translation '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
