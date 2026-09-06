<?php

declare(strict_types=1);

namespace App\Services\Tax;

use App\Models\Accounting\Company;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxRate;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Carbon;

/**
 * Configurable tax computation — never hard-coded rates (spec §22).
 *
 * @return array{tax: numeric-string, rate: numeric-string, gl_account: string, legal_reference: string}
 */
class TaxEngine
{
    /**
     * @return array{tax: numeric-string, rate: numeric-string, gl_account: string, legal_reference: string}
     */
    public function compute(Company $company, string $taxCode, string|float|int $net, Carbon|string $date): array
    {
        $code = TaxCode::query()
            ->where('company_id', $company->id)
            ->where('code', $taxCode)
            ->where('is_active', true)
            ->first();

        if ($code === null) {
            throw new PostingException("Tax code '{$taxCode}' is not configured for company {$company->code}.");
        }

        $asOf = Carbon::parse($date)->toDateString();
        $rate = TaxRate::query()
            ->where('tax_code_id', $code->id)
            ->where('status', 'approved')
            ->whereDate('effective_from', '<=', $asOf)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $asOf))
            ->orderByDesc('effective_from')
            ->first();

        if ($rate === null) {
            throw new PostingException("No approved rate for tax code '{$taxCode}' on {$asOf} (legal configuration required).");
        }

        $netAmt = Decimal::of($net);
        $pct = Decimal::div(Decimal::of((string) $rate->rate), '100');

        return [
            'tax' => Decimal::mul($netAmt, $pct),
            'rate' => Decimal::of((string) $rate->rate),
            'gl_account' => $code->gl_account_code,
            'legal_reference' => $rate->legal_reference,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function defineCode(Company $company, array $attributes): TaxCode
    {
        return TaxCode::create([
            'company_id' => $company->id,
            'code' => $attributes['code'],
            'name' => $attributes['name'] ?? $attributes['code'],
            'kind' => $attributes['kind'],
            'gl_account_code' => $attributes['gl_account_code'],
            'is_active' => $attributes['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function approveRate(TaxCode $code, array $attributes): TaxRate
    {
        if (! isset($attributes['legal_reference']) || trim((string) $attributes['legal_reference']) === '') {
            throw new PostingException('A tax rate requires a legal_reference; rates are never assumed.');
        }

        return TaxRate::create([
            'tax_code_id' => $code->id,
            'rate' => Decimal::of((string) $attributes['rate']),
            'effective_from' => $attributes['effective_from'],
            'effective_to' => isset($attributes['effective_to']) && trim((string) $attributes['effective_to']) !== '' ? $attributes['effective_to'] : null,
            'legal_reference' => $attributes['legal_reference'],
            'authority' => $attributes['authority'] ?? null,
            'status' => 'approved',
        ]);
    }
}
