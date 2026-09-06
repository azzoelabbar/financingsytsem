<?php

declare(strict_types=1);

namespace App\Services\Accounting\Exceptions;

use RuntimeException;

/**
 * Base for all accounting-integrity violations. These represent broken invariants
 * of the double-entry engine (spec §57) and must never be silently swallowed.
 */
class AccountingException extends RuntimeException {}
