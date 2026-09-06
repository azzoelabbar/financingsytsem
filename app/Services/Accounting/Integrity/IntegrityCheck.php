<?php

declare(strict_types=1);

namespace App\Services\Accounting\Integrity;

/**
 * The result of one accounting-integrity invariant (e.g. INV-1 Debits=Credits).
 * A discrepancy is never swallowed — it is reported here and, via
 * IntegrityService::assert(), raised as an exception.
 */
final readonly class IntegrityCheck
{
    public const PASS = 'pass';

    public const FAIL = 'fail';

    public const SKIPPED = 'skipped';

    public function __construct(
        public string $code,        // INV-1, INV-2, ...
        public string $name,
        public string $status,      // pass | fail | skipped
        public ?string $expected = null,
        public ?string $actual = null,
        public ?string $difference = null,
        public ?string $message = null,
    ) {}

    public function passed(): bool
    {
        return $this->status === self::PASS;
    }

    public function failed(): bool
    {
        return $this->status === self::FAIL;
    }
}
