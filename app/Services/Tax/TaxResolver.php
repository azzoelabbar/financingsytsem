<?php

declare(strict_types=1);

namespace App\Services\Tax;

use App\Models\Accounting\Company;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Localization\RuleResolver;
use Illuminate\Support\Carbon;

/**
 * Resolves tax amounts from versioned configuration (TaxEngine or country pack).
 * Never embeds a numeric rate in callers.
 */
class TaxResolver
{
    public function __construct(
        private readonly TaxEngine $engine,
        private readonly RuleResolver $localization,
    ) {}

    /**
     * @return array{tax: numeric-string, rate: numeric-string, gl_account: ?string, legal_reference: string, source: string}
     */
    public function compute(Company $company, string $code, string|float|int $net, Carbon|string $date, ?string $country = null): array
    {
        if ($country !== null) {
            $rule = $this->localization->resolve($country, $code, Carbon::parse($date));
            if ($rule->rate === null) {
                throw new PostingException("Localization tax rule '{$code}' has no sourced rate.");
            }
            $raw = Decimal::of((string) $rule->rate);
            // Values > 1 are percent (e.g. 15); values ≤ 1 are already fractions (e.g. 0.15).
            $pct = Decimal::compare($raw, '1') > 0 ? Decimal::div($raw, '100') : $raw;

            return [
                'tax' => Decimal::mul(Decimal::of($net), $pct),
                'rate' => $raw,
                'gl_account' => is_array($rule->meta) && is_string($rule->meta['gl_account'] ?? null) ? $rule->meta['gl_account'] : null,
                'legal_reference' => (string) $rule->legal_reference,
                'source' => 'localization',
            ];
        }

        $computed = $this->engine->compute($company, $code, $net, $date);

        return [
            'tax' => $computed['tax'],
            'rate' => $computed['rate'],
            'gl_account' => $computed['gl_account'],
            'legal_reference' => $computed['legal_reference'],
            'source' => 'tax_engine',
        ];
    }
}
