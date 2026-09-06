<?php

declare(strict_types=1);

namespace App\Services\Accounting\Integrity\Exceptions;

use App\Services\Accounting\Exceptions\AccountingException;
use App\Services\Accounting\Integrity\IntegrityCheck;

/**
 * Raised when one or more accounting invariants are violated (spec: any
 * difference must surface as an exception). Carries the failed checks.
 */
class IntegrityViolationException extends AccountingException
{
    /** @param list<IntegrityCheck> $failures */
    public function __construct(public readonly array $failures)
    {
        $lines = array_map(
            fn (IntegrityCheck $c): string => "{$c->code} {$c->name}: expected {$c->expected}, actual {$c->actual} (diff {$c->difference})",
            $failures,
        );

        parent::__construct('Accounting integrity violated: '.implode(' | ', $lines));
    }
}
