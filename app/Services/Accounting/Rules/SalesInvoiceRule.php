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
 * Sales invoice → revenue recognition journal (spec §10):
 *   Dr Accounts Receivable (gross)
 *      Cr Revenue (net, per line)
 *      Cr Output VAT (tax, per line)
 *
 * Data-driven: the document carries the AR control account, the per-line revenue
 * and tax accounts, and the tax amount (caller-supplied; Tax Engine computes it
 * in Phase F). A legacy single net/vat payload is still supported so the original
 * engine boundary test keeps working.
 *
 * Payload (line-based):
 *   ar_account, book_basis, lines: [{ revenue_account, tax_account, net, tax, dimensions }]
 * Payload (legacy): net, vat, accounts:{ receivable, revenue, output_vat }
 */
final class SalesInvoiceRule implements AccountingRule
{
    private const RECEIVABLE = '110201';

    private const REVENUE = '410101';

    private const OUTPUT_VAT = '210401';

    public function type(): string
    {
        return 'sales.invoice';
    }

    public function book(): BookBasis
    {
        return BookBasis::LOCAL;
    }

    public function version(): string
    {
        return '1.1';
    }

    public function build(SourceDocument $document): JournalDraft
    {
        $payload = $document->payload();
        $book = is_string($payload['book_basis'] ?? null) ? $payload['book_basis'] : BookBasis::LOCAL->value;
        $arAccount = is_string($payload['ar_account'] ?? null) ? $payload['ar_account'] : self::RECEIVABLE;
        $docDimensions = $document->dimensions();

        $creditMovements = [];
        $gross = '0';

        if (is_array($payload['lines'] ?? null) && $payload['lines'] !== []) {
            /** @var array<int, array<string, mixed>> $lines */
            $lines = $payload['lines'];
            foreach ($lines as $line) {
                $revenueAccount = is_string($line['revenue_account'] ?? null) ? $line['revenue_account'] : self::REVENUE;
                $taxAccount = is_string($line['tax_account'] ?? null) ? $line['tax_account'] : self::OUTPUT_VAT;
                /** @var array<string, string> $lineDimensions */
                $lineDimensions = is_array($line['dimensions'] ?? null) ? $line['dimensions'] : $docDimensions;
                $net = Decimal::of($this->scalar($line, 'net'));
                $tax = Decimal::of($this->scalar($line, 'tax', '0'));

                $creditMovements[] = LedgerMovement::credit($revenueAccount, $net, $lineDimensions, 'Sales revenue');
                if (Decimal::isPositive($tax)) {
                    $creditMovements[] = LedgerMovement::credit($taxAccount, $tax, $lineDimensions, 'Output VAT');
                }
                $gross = Decimal::add($gross, Decimal::add($net, $tax));
            }
        } else {
            // Legacy single net/vat payload.
            $accounts = is_array($payload['accounts'] ?? null) ? $payload['accounts'] : [];
            $revenue = is_string($accounts['revenue'] ?? null) ? $accounts['revenue'] : self::REVENUE;
            $outputVat = is_string($accounts['output_vat'] ?? null) ? $accounts['output_vat'] : self::OUTPUT_VAT;
            $arAccount = is_string($accounts['receivable'] ?? null) ? $accounts['receivable'] : $arAccount;
            $net = Decimal::of($this->scalar($payload, 'net'));
            $tax = Decimal::of($this->scalar($payload, 'vat', '0'));

            $creditMovements[] = LedgerMovement::credit($revenue, $net, $docDimensions, 'Sales revenue');
            if (Decimal::isPositive($tax)) {
                $creditMovements[] = LedgerMovement::credit($outputVat, $tax, $docDimensions, 'Output VAT');
            }
            $gross = Decimal::add($net, $tax);
        }

        $movements = array_merge(
            [LedgerMovement::debit($arAccount, $gross, $docDimensions, 'Trade receivable')],
            $creditMovements,
        );

        return new JournalDraft(
            book: $book,
            source: 'sales',
            date: $document->date(),
            reference: $document->reference(),
            description: 'Sales invoice '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function scalar(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }
}
