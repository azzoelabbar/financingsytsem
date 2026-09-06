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
 * Period accrual → Dr Expense · Cr Accrued Liability.
 * Payload: expense_account, accrual_account, amount, book_basis.
 */
final class AccrualRule implements AccountingRule
{
    public function type(): string
    {
        return 'gl.accrual';
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
        $accrual = is_string($payload['accrual_account'] ?? null) ? $payload['accrual_account'] : '';
        if ($expense === '' || $accrual === '') {
            throw new PostingException('Accrual requires expense_account and accrual_account from the chart.');
        }
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'accrual',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Accrual '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($expense, $amount, $dims, 'Accrued expense'),
                LedgerMovement::credit($accrual, $amount, $dims, 'Accrued liability'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
