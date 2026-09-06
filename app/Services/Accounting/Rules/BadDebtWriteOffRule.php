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
 * Bad-debt write-off:
 *   covered by allowance → Dr Loss Allowance · Cr AR   (no P&L hit; loss taken via ECL earlier)
 *   uncovered            → Dr Bad-Debt Expense · Cr AR
 * Payload: ar_account, allowance_account, expense_account, amount, covered_by_allowance, book_basis.
 */
final class BadDebtWriteOffRule implements AccountingRule
{
    public function type(): string
    {
        return 'ar.bad_debt';
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
        $allowance = is_string($payload['allowance_account'] ?? null) ? $payload['allowance_account'] : '110204';
        $expense = is_string($payload['expense_account'] ?? null) ? $payload['expense_account'] : '630201';
        $covered = (bool) ($payload['covered_by_allowance'] ?? true);
        $amount = Decimal::of(is_scalar($payload['amount'] ?? null) ? (string) $payload['amount'] : '0');
        $dims = $document->dimensions();

        $debitAccount = $covered ? $allowance : $expense;

        return new JournalDraft(
            book: $book,
            source: 'ar_bad_debt',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Bad debt write-off '.($document->reference() ?? ''),
            movements: [
                LedgerMovement::debit($debitAccount, $amount, $dims, $covered ? 'Use loss allowance' : 'Bad-debt expense'),
                LedgerMovement::credit($arAccount, $amount, $dims, 'Write off receivable'),
            ],
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
