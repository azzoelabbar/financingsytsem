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
 * Expected Credit Loss provision (IFRS 9):
 *   Dr ECL Expense · Cr Loss Allowance (contra-AR).
 * The amount is the incremental allowance produced by the (configurable) ECL
 * methodology — never computed here. Payload: expense_account, allowance_account,
 * amount, book_basis.
 */
final class EclRule implements AccountingRule
{
    public function type(): string
    {
        return 'ar.ecl';
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
        $expense = is_string($payload['expense_account'] ?? null) ? $payload['expense_account'] : '630203';
        $allowance = is_string($payload['allowance_account'] ?? null) ? $payload['allowance_account'] : '110204';
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        return new JournalDraft(
            book: $book,
            source: 'ar_ecl',
            date: $document->date(),
            reference: $document->reference(),
            description: 'ECL provision '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($expense, $amount, $dims, 'ECL expense'),
                LedgerMovement::credit($allowance, $amount, $dims, 'Loss allowance'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
