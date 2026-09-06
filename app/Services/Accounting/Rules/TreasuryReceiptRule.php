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
 * Treasury cash/bank receipt → Dr Bank|Cash · Cr contra (income/clearing).
 * Payload: treasury_account, counter_account, amount, book_basis.
 */
final class TreasuryReceiptRule implements AccountingRule
{
    public function type(): string
    {
        return 'treasury.receipt';
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
        $treasury = is_string($payload['treasury_account'] ?? null) ? $payload['treasury_account'] : '';
        $counter = is_string($payload['counter_account'] ?? null) ? $payload['counter_account'] : '';
        if ($treasury === '' || $counter === '') {
            throw new PostingException('Treasury receipt requires treasury_account and counter_account from the chart.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'treasury',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Treasury receipt '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($treasury, $amount, $dims, 'Cash/bank received'),
                LedgerMovement::credit($counter, $amount, $dims, 'Receipt contra'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
