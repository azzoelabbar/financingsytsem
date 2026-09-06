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
 * Prepayment amortization → Dr Expense · Cr Prepaid.
 * Payload: expense_account, prepaid_account, amount, book_basis.
 */
final class PrepaymentAmortizationRule implements AccountingRule
{
    public function type(): string
    {
        return 'gl.prepayment_amortization';
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
        $expense = is_string($payload['expense_account'] ?? null) ? $payload['expense_account'] : '';
        $prepaid = is_string($payload['prepaid_account'] ?? null) ? $payload['prepaid_account'] : '';
        if ($expense === '' || $prepaid === '') {
            throw new PostingException('Amortization requires expense_account and prepaid_account from the chart.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'prepayment_amortization',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Prepayment amortization '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($expense, $amount, $dims, 'Expense recognition'),
                LedgerMovement::credit($prepaid, $amount, $dims, 'Release prepaid'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
