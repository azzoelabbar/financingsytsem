<?php

declare(strict_types=1);

namespace App\Services\Accounting\Rules;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Contracts\AccountingRule;
use App\Services\Accounting\Contracts\SourceDocument;
use App\Services\Accounting\Data\JournalDraft;
use App\Services\Accounting\Data\LedgerMovement;
use App\Services\Accounting\Support\Decimal;

/**
 * Customer receipt → Dr Bank/Cash, Cr Accounts Receivable.
 * Payload: ar_account, cash_bank_account, amount, book_basis.
 */
final class CustomerReceiptRule implements AccountingRule
{
    public function type(): string
    {
        return 'customer.receipt';
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
        $arAccount = is_string($payload['ar_account'] ?? null) ? $payload['ar_account'] : '110201';
        $cashBank = is_string($payload['cash_bank_account'] ?? null) ? $payload['cash_bank_account'] : '110102';
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'ar_receipt',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Customer receipt '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($cashBank, $amount, $dims, 'Cash/bank received'),
                LedgerMovement::credit($arAccount, $amount, $dims, 'Settle receivable'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
