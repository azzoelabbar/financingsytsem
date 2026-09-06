<?php

declare(strict_types=1);

namespace App\Services\Fx;

use App\Enums\Accounting\RateType;
use App\Models\Accounting\Company;
use App\Models\Accounting\Currency;
use App\Models\Accounting\ExchangeRate;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Support\Decimal;
use App\Services\Fx\Exceptions\FxException;
use Illuminate\Support\Carbon;

/**
 * Exchange-rate master: store and resolve (from,to,type,date) with prior-date fallback.
 * Rates are quoted as units of to_currency per 1 unit of from_currency.
 */
class ExchangeRateService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function setRate(Company $company, string $from, string $to, string|float|int $rate, Carbon|string $date, RateType|string $type = RateType::SPOT, ?string $source = null): ExchangeRate
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $this->assertCurrency($from);
        $this->assertCurrency($to);
        if ($from === $to) {
            throw new FxException('from_currency and to_currency must differ.');
        }

        $normalized = Decimal::of($rate);
        if (! Decimal::isPositive($normalized)) {
            throw new FxException('Exchange rate must be positive.');
        }

        $rateType = $type instanceof RateType ? $type : RateType::from($type);
        $rateDate = Carbon::parse($date)->toDateString();

        $row = ExchangeRate::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'from_currency' => $from,
                'to_currency' => $to,
                'rate_type' => $rateType->value,
                'rate_date' => $rateDate,
            ],
            [
                'rate' => $normalized,
                'source' => $source,
            ],
        );

        $this->audit->record($row, 'rate_set', $company->id, null, [
            'from' => $from,
            'to' => $to,
            'type' => $rateType->value,
            'date' => $rateDate,
            'rate' => $normalized,
        ]);

        return $row;
    }

    /**
     * Resolve rate for converting from → to (typically FC → functional).
     * Falls back to the latest rate on or before the date; tries inverse pair if needed.
     *
     * @return numeric-string
     */
    public function resolve(
        Company $company,
        string $from,
        string $to,
        Carbon|string $date,
        RateType|string $type = RateType::SPOT,
    ): string {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from === $to) {
            return Decimal::of('1');
        }

        $rateType = $type instanceof RateType ? $type : RateType::from($type);
        $asOf = Carbon::parse($date)->toDateString();

        $direct = ExchangeRate::query()
            ->where('company_id', $company->id)
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->where('rate_type', $rateType->value)
            ->whereDate('rate_date', '<=', $asOf)
            ->orderByDesc('rate_date')
            ->first();

        if ($direct !== null) {
            return Decimal::of((string) $direct->rate);
        }

        $inverse = ExchangeRate::query()
            ->where('company_id', $company->id)
            ->where('from_currency', $to)
            ->where('to_currency', $from)
            ->where('rate_type', $rateType->value)
            ->whereDate('rate_date', '<=', $asOf)
            ->orderByDesc('rate_date')
            ->first();

        if ($inverse !== null) {
            return Decimal::div('1', Decimal::of((string) $inverse->rate));
        }

        // Spot fallback for closing/average when that type is missing.
        if ($rateType !== RateType::SPOT) {
            return $this->resolve($company, $from, $to, $asOf, RateType::SPOT);
        }

        throw new FxException("No {$rateType->value} rate for {$from}/{$to} on or before {$asOf}.");
    }

    /**
     * @return numeric-string
     */
    public function convert(
        Company $company,
        string|float|int $amount,
        string $from,
        string $to,
        Carbon|string $date,
        RateType|string $type = RateType::SPOT,
    ): string {
        $rate = $this->resolve($company, $from, $to, $date, $type);

        return Decimal::mul(Decimal::of($amount), $rate);
    }

    private function assertCurrency(string $code): void
    {
        if (! Currency::query()->where('code', $code)->where('is_active', true)->exists()) {
            throw new FxException("Currency '{$code}' is not an active currency.");
        }
    }
}
