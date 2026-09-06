<?php

declare(strict_types=1);

namespace App\Services\Treasury\Exceptions;

class ReconciliationException extends TreasuryException
{
    public static function mismatch(string $adjusted, string $book): self
    {
        return new self("Bank reconciliation failed (INV-6): adjusted statement {$adjusted} ≠ book {$book}.");
    }

    public static function incomplete(): self
    {
        return new self('Cannot complete reconciliation while unmatched statement lines remain (or force unmatched review).');
    }
}
