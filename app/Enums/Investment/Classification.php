<?php

declare(strict_types=1);

namespace App\Enums\Investment;

enum Classification: string
{
    case FVTPL = 'FVTPL';
    case FVOCI = 'FVOCI';
    case AMORTIZED_COST = 'AMORTIZED_COST';

    public static function parse(string $value): self
    {
        $normalized = strtoupper(str_replace(['-', ' '], '_', $value));
        $normalized = match ($normalized) {
            'AMORTIZED' => 'AMORTIZED_COST',
            default => $normalized,
        };

        return self::from($normalized);
    }

    public function glRole(): string
    {
        return match ($this) {
            self::FVTPL => 'investment.fvtpl',
            self::FVOCI => 'investment.fvoci',
            self::AMORTIZED_COST => 'investment.amortized',
        };
    }
}
