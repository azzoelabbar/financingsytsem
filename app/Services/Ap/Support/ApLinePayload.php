<?php

declare(strict_types=1);

namespace App\Services\Ap\Support;

use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Tax\TaxEngine;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared mapping of AP document lines onto the payload the accounting rules consume.
 */
final class ApLinePayload
{
    /**
     * @param  iterable<int, Model>  $lines
     * @return array<int, array{expense_account: string, tax_account: string, net: string, tax: string, dimensions: array<string, string>}>
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
                'expense_account' => (string) $l->getAttribute('expense_account_code'),
                'tax_account' => is_string($taxAccount) ? $taxAccount : '',
                'net' => (string) $l->getAttribute('net_amount'),
                'tax' => (string) $l->getAttribute('tax_amount'),
                'dimensions' => $dimensions,
            ];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array{net: numeric-string, tax: numeric-string, attributes: array<string, mixed>}
     */
    public static function normalizeInput(array $line, int $lineNo, ?string $defaultExpense = null, ?Company $company = null, ?string $date = null): array
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

        $expense = is_string($line['expense_account'] ?? null) ? $line['expense_account'] : $defaultExpense;
        if ($expense === null || $expense === '') {
            throw new PostingException("Line {$lineNo} must carry an expense or inventory account code.");
        }

        return [
            'net' => $net,
            'tax' => $tax,
            'attributes' => [
                'line_no' => $lineNo,
                'description' => $line['description'] ?? null,
                'expense_account_code' => $expense,
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
