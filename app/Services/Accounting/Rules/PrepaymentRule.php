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
 * Prepayment recognition → Dr Prepaid · Cr Bank/Cash.
 * Payload: prepaid_account, funding_account, amount, book_basis.
 */
final class PrepaymentRule implements AccountingRule
{
    public function type(): string
    {
        return 'gl.prepayment';
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
        $prepaid = is_string($payload['prepaid_account'] ?? null) ? $payload['prepaid_account'] : '';
        $funding = is_string($payload['funding_account'] ?? null) ? $payload['funding_account'] : '';
        if ($prepaid === '' || $funding === '') {
            throw new PostingException('Prepayment requires prepaid_account and funding_account from the chart.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'prepayment',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Prepayment '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($prepaid, $amount, $dims, 'Prepaid expense'),
                LedgerMovement::credit($funding, $amount, $dims, 'Bank/cash paid'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
