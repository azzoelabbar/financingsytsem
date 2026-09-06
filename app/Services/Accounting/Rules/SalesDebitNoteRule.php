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
 * Sales debit note → additional charge to the customer (same shape as an invoice):
 *   Dr Accounts Receivable (gross) · Cr Revenue (net) · Cr Output VAT (tax).
 * Payload: ar_account, book_basis, lines: [{ revenue_account, tax_account, net, tax, dimensions }].
 */
final class SalesDebitNoteRule implements AccountingRule
{
    public function type(): string
    {
        return 'sales.debit_note';
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
        $docDimensions = $document->dimensions();

        $creditMovements = [];
        $gross = '0';
        /** @var array<int, array<string, mixed>> $lines */
        $lines = is_array($payload['lines'] ?? null) ? $payload['lines'] : [];

        foreach ($lines as $line) {
            $revenueAccount = is_string($line['revenue_account'] ?? null) ? $line['revenue_account'] : '410101';
            $taxAccount = is_string($line['tax_account'] ?? null) ? $line['tax_account'] : '210401';
            /** @var array<string, string> $lineDimensions */
            $lineDimensions = is_array($line['dimensions'] ?? null) ? $line['dimensions'] : $docDimensions;
            $net = Decimal::of(is_scalar($line['net'] ?? null) ? (string) $line['net'] : '0');
            $tax = Decimal::of(is_scalar($line['tax'] ?? null) ? (string) $line['tax'] : '0');

            $creditMovements[] = LedgerMovement::credit($revenueAccount, $net, $lineDimensions, 'Additional charge');
            if (Decimal::isPositive($tax)) {
                $creditMovements[] = LedgerMovement::credit($taxAccount, $tax, $lineDimensions, 'Output VAT');
            }
            $gross = Decimal::add($gross, Decimal::add($net, $tax));
        }

        $movements = array_merge(
            [LedgerMovement::debit($arAccount, $gross, $docDimensions, 'Trade receivable')],
            $creditMovements,
        );

        return new JournalDraft(
            book: $book,
            source: 'sales_debit_note',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Sales debit note '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
