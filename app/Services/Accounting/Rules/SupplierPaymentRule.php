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
 * Supplier payment → Dr Accounts Payable · Cr Bank/Cash.
 * Payload: ap_account, cash_bank_account, amount, book_basis.
 */
final class SupplierPaymentRule implements AccountingRule
{
    public function type(): string
    {
        return 'supplier.payment';
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
        $apAccount = is_string($payload['ap_account'] ?? null) ? $payload['ap_account'] : '';
        $cashBank = is_string($payload['cash_bank_account'] ?? null) ? $payload['cash_bank_account'] : '';
        if ($apAccount === '' || $cashBank === '') {
            throw new PostingException('Supplier payment payload must include the AP control and cash/bank accounts from the chart.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'ap_payment',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Supplier payment '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($apAccount, $amount, $dims, 'Settle payable'),
                LedgerMovement::credit($cashBank, $amount, $dims, 'Cash/bank paid'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
