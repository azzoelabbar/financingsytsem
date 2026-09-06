<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\Localization\LocalizationRule;
use App\Services\Accounting\Support\Decimal;
use App\Services\Localization\Exceptions\RuleNotFoundException;
use Illuminate\Support\Carbon;

/**
 * The single way the platform reads a country's legal/tax rules. The accounting
 * core, tax engine, payroll and reporting all call this — none of them hold a
 * rate, threshold, deadline or document rule of their own (spec §23, §62).
 *
 * Resolution picks the newest ACTIVE version whose effective window contains the
 * date. There is no hidden numeric fallback: a missing rule throws.
 */
class RuleResolver
{
    /** Resolve the active rule for a country/code on a date (default: today). */
    public function resolve(string $country, string $ruleCode, ?Carbon $onDate = null): LocalizationRule
    {
        $date = $onDate ?? Carbon::now();

        $rule = LocalizationRule::query()
            ->where('country', $country)
            ->where('rule_code', $ruleCode)
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('version')
            ->first();

        return $rule ?? throw RuleNotFoundException::make($country, $ruleCode, $date->toDateString());
    }

    /**
     * Resolve a rate value (as a numeric string). Throws if the rule exists but
     * its value has not been sourced yet — never returns a guessed number.
     *
     * @return numeric-string
     */
    public function rate(string $country, string $ruleCode, ?Carbon $onDate = null): string
    {
        $rule = $this->resolve($country, $ruleCode, $onDate);

        if ($rule->rate === null) {
            throw RuleNotFoundException::noValue($country, $ruleCode);
        }

        return Decimal::of($rule->rate);
    }

    public function has(string $country, string $ruleCode, ?Carbon $onDate = null): bool
    {
        try {
            $this->resolve($country, $ruleCode, $onDate);

            return true;
        } catch (RuleNotFoundException) {
            return false;
        }
    }
}
