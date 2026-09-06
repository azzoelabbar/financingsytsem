<?php

declare(strict_types=1);

namespace App\Services\Gl\Exceptions;

final class PeriodCloseException extends GlException
{
    public static function checklistFailed(): self
    {
        return new self('Period close checklist has failing items; cannot close the period.');
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Invalid period status transition from '{$from}' to '{$to}'.");
    }

    public static function reopenLocked(): self
    {
        return new self('A locked period cannot be reopened without a dedicated unlock process.');
    }

    public static function reopenRequiresReason(): self
    {
        return new self('Reopening a fiscal period requires a non-empty reason (audit SoD-5).');
    }
}
