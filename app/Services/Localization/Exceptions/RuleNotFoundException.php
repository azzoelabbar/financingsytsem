<?php

declare(strict_types=1);

namespace App\Services\Localization\Exceptions;

use RuntimeException;

/**
 * Raised when no ACTIVE rule can be resolved for a (country, code, date). The
 * layer never returns a hidden default — a missing legal rule is an explicit
 * error the caller must handle (spec §23).
 */
class RuleNotFoundException extends RuntimeException
{
    public static function make(string $country, string $ruleCode, string $date): self
    {
        return new self("No active localization rule '{$ruleCode}' for country '{$country}' effective on {$date}.");
    }

    public static function noValue(string $country, string $ruleCode): self
    {
        return new self("Localization rule '{$ruleCode}' ({$country}) has no rate value set yet — populate it from its official source.");
    }
}
