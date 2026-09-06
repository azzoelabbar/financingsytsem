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
 * Inter-account transfer → Dr destination · Cr source (zero P&L).
 * Payload: from_account, to_account, amount, book_basis.
 */
final class TreasuryTransferRule implements AccountingRule
{
    public function type(): string
    {
        return 'treasury.transfer';
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
        $from = is_string($payload['from_account'] ?? null) ? $payload['from_account'] : '';
        $to = is_string($payload['to_account'] ?? null) ? $payload['to_account'] : '';
        if ($from === '' || $to === '') {
            throw new PostingException('Treasury transfer requires from_account and to_account from the chart.');
        }
        if ($from === $to) {
            throw new PostingException('Treasury transfer source and destination must differ.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'treasury_transfer',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Treasury transfer '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($to, $amount, $dims, 'Transfer in'),
                LedgerMovement::credit($from, $amount, $dims, 'Transfer out'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
