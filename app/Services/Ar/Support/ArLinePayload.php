<?php

declare(strict_types=1);

namespace App\Services\Ar\Support;

use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Tax\TaxEngine;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared mapping of AR document lines onto the payload the accounting rules
 * consume. Tax is caller-supplied (Tax Engine, Phase F, later computes it).
 */
final class ArLinePayload
{
    /**
     * @param  iterable<int, Model>  $lines
     * @return array<int, array{revenue_account: string, tax_account: string, net: string, tax: string, dimensions: array<string, string>}>
     */
    public static function fromLines(iterable $lines): array
    {
        $payload = [];

        foreach ($lines as $l) {
            $rawDimensions = $l->getAttribute('dimensions');
            $dimensions = [];
            if (is_array($rawDimensions)) {
                foreach ($rawDimensions as $code => $value) {
                    if (is_string($code) && is_string($value)) {
                        $dimensions[$code] = $value;
                    }
                }
            }
            $taxAccount = $l->getAttribute('tax_account_code');

            $payload[] = [
                'revenue_account' => (string) $l->getAttribute('revenue_account_code'),
                'tax_account' => is_string($taxAccount) ? $taxAccount : '210401',
                'net' => (string) $l->getAttribute('net_amount'),
                'tax' => (string) $l->getAttribute('tax_amount'),
                'dimensions' => $dimensions,
            ];
        }

        return $payload;
    }

    /**
     * Persist one commercial line (invoice / credit note / debit note).
     *
     * @param  array<string, mixed>  $line
     * @return array{net: numeric-string, tax: numeric-string, attributes: array<string, mixed>}
     */
    public static function normalizeInput(array $line, int $lineNo, ?Company $company = null, ?string $date = null): array
    {
        $qty = Decimal::of(is_scalar($line['quantity'] ?? null) ? (string) $line['quantity'] : '1');
        $price = Decimal::of(is_scalar($line['unit_price'] ?? null) ? (string) $line['unit_price'] : '0');
        $net = isset($line['net']) && is_scalar($line['net']) ? Decimal::of((string) $line['net']) : Decimal::mul($qty, $price);
        $tax = Decimal::of(is_scalar($line['tax'] ?? null) ? (string) $line['tax'] : '0');

        if (is_string($line['tax_code'] ?? null) && $company !== null && $date !== null && ! array_key_exists('tax', $line)) {
            $computed = app(TaxEngine::class)->compute($company, $line['tax_code'], $net, $date);
            $tax = $computed['tax'];
            $line['tax_account'] = is_string($line['tax_account'] ?? null) ? $line['tax_account'] : $computed['gl_account'];
        }

        if (! Decimal::isPositive($net) && ! Decimal::isPositive($tax)) {
            throw new PostingException("Line {$lineNo} must carry a positive net or tax amount.");
        }

        if (Decimal::isPositive($tax) && ! is_string($line['tax_account'] ?? null)) {
            throw new PostingException("Line {$lineNo} carries tax but no tax account code (configure a tax_code or pass tax_account).");
        }

        if (! isset($line['revenue_account']) || ! is_string($line['revenue_account']) || $line['revenue_account'] === '') {
            throw new PostingException("Line {$lineNo} must carry a revenue account code.");
        }

        return [
            'net' => $net,
            'tax' => $tax,
            'attributes' => [
                'line_no' => $lineNo,
                'description' => $line['description'] ?? null,
                'revenue_account_code' => $line['revenue_account'],
                'item_ref' => $line['item_ref'] ?? null,
                'quantity' => $qty,
                'unit_price' => $price,
                'net_amount' => $net,
                'tax_code' => $line['tax_code'] ?? null,
                'tax_account_code' => $line['tax_account'] ?? null,
                'tax_amount' => $tax,
                'dimensions' => is_array($line['dimensions'] ?? null) ? $line['dimensions'] : null,
            ],
        ];
    }

    /** @param  numeric-string  $gross */
    public static function assertPositiveGross(string $gross): void
    {
        if (! Decimal::isPositive($gross)) {
            throw new PostingException('Document gross total must be positive.');
        }
    }
}
