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
 * Purchase debit note → additional charge (same shape as a purchase invoice):
 *   Dr Expense/Inventory · Dr Input Tax · Cr AP.
 */
final class PurchaseDebitNoteRule implements AccountingRule
{
    public function type(): string
    {
        return 'purchase.debit_note';
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
        if ($apAccount === '') {
            throw new PostingException('Purchase debit note payload must include the AP control account resolved from the chart.');
        }

        $docDimensions = $document->dimensions();
        $debitMovements = [];
        $gross = '0';
        /** @var array<int, array<string, mixed>> $lines */
        $lines = is_array($payload['lines'] ?? null) ? $payload['lines'] : [];

        foreach ($lines as $line) {
            $expenseAccount = is_string($line['expense_account'] ?? null) ? $line['expense_account'] : '';
            if ($expenseAccount === '') {
                throw new PostingException('Each debit-note line must carry an expense or inventory account.');
            }
            $taxAccount = is_string($line['tax_account'] ?? null) ? $line['tax_account'] : '';
            /** @var array<string, string> $lineDimensions */
            $lineDimensions = is_array($line['dimensions'] ?? null) ? $line['dimensions'] : $docDimensions;
            $net = Decimal::of(is_scalar($line['net'] ?? null) ? (string) $line['net'] : '0');
            $tax = Decimal::of(is_scalar($line['tax'] ?? null) ? (string) $line['tax'] : '0');

            $debitMovements[] = LedgerMovement::debit($expenseAccount, $net, $lineDimensions, 'Additional charge');
            if (Decimal::isPositive($tax)) {
                if ($taxAccount === '') {
                    throw new PostingException('A debit-note line with tax must carry a tax account code.');
                }
                $debitMovements[] = LedgerMovement::debit($taxAccount, $tax, $lineDimensions, 'Input VAT');
            }
            $gross = Decimal::add($gross, Decimal::add($net, $tax));
        }

        $movements = array_merge($debitMovements, [
            LedgerMovement::credit($apAccount, $gross, $docDimensions, 'Trade payable'),
        ]);

        return new JournalDraft(
            book: $book,
            source: 'purchase_debit_note',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Purchase debit note '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
