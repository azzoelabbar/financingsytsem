<?php

declare(strict_types=1);

namespace App\Services\Accounting\Data;

use App\Services\Accounting\Support\Decimal;

/**
 * An immutable, balanced-by-construction set of ledger movements produced by an
 * AccountingRule and targeted at one book. The engine re-checks the balance
 * before posting (the balance invariant is never trusted blindly).
 */
final readonly class JournalDraft
{
    /** @param list<LedgerMovement> $movements */
    public function __construct(
        public string $book,          // BookBasis value: local | ifrs | tax
        public string $source,        // sales | purchase | payroll | ...
        public string $date,          // Y-m-d
        public ?string $reference,
        public string $description,
        public array $movements,
        public string $ruleVersion,
        public ?string $currency = null,
        public float $exchangeRate = 1.0,
    ) {}

    /**
     * Transaction-to-functional rate carried on the source document (Phase A
     * conversion). Cross-currency settlement gain/loss is Phase E.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function rateFromPayload(array $payload): float
    {
        return is_numeric($payload['exchange_rate'] ?? null) ? (float) $payload['exchange_rate'] : 1.0;
    }

    /** @return numeric-string */
    public function totalDebit(): string
    {
        $t = '0';
        foreach ($this->movements as $m) {
            $t = Decimal::add($t, Decimal::of($m->debit));
        }

        return $t;
    }

    /** @return numeric-string */
    public function totalCredit(): string
    {
        $t = '0';
        foreach ($this->movements as $m) {
            $t = Decimal::add($t, Decimal::of($m->credit));
        }

        return $t;
    }

    public function isBalanced(): bool
    {
        return Decimal::equals($this->totalDebit(), $this->totalCredit());
    }
}
