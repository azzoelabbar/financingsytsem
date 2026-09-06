<?php

declare(strict_types=1);

namespace App\Services\Accounting\Exceptions;

class UnbalancedJournalException extends AccountingException
{
    public static function make(string $debit, string $credit): self
    {
        return new self(
            "Journal is not balanced: total debit ({$debit}) must equal total credit ({$credit})."
        );
    }
}
