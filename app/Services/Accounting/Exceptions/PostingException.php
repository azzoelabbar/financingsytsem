<?php

declare(strict_types=1);

namespace App\Services\Accounting\Exceptions;

/**
 * Raised when a posting is rejected: a closed period, a non-posting (summary)
 * account, a missing mandatory dimension, an invalid line, or an already-posted
 * journal. Groups the specific rule violations of the posting workflow.
 */
class PostingException extends AccountingException
{
    public static function closedPeriod(string $date): self
    {
        return new self("Cannot post: no OPEN fiscal period contains the date {$date}.");
    }

    public static function nonPostingAccount(string $code): self
    {
        return new self("Cannot post to account {$code}: it is a summary/non-posting account.");
    }

    public static function manualNotAllowed(string $code): self
    {
        return new self("Cannot post a manual journal to account {$code}: manual journals are not allowed on it.");
    }

    public static function accountInactive(string $code): self
    {
        return new self("Cannot post to account {$code}: it is inactive or outside its effective dates.");
    }

    public static function missingDimension(string $code, string $dimension): self
    {
        return new self("Cannot post to account {$code}: mandatory dimension '{$dimension}' is missing.");
    }

    public static function alreadyPosted(): self
    {
        return new self('This journal is already posted; posted journals are immutable and can only be reversed.');
    }

    public static function notPosted(): self
    {
        return new self('Only a posted journal can be reversed.');
    }

    public static function alreadyReversed(): self
    {
        return new self('This journal has already been reversed.');
    }

    public static function emptyJournal(): self
    {
        return new self('A journal must contain at least two lines.');
    }

    public static function lineNotDebitXorCredit(int $lineNo): self
    {
        return new self("Line {$lineNo} must carry a positive amount in exactly one of debit or credit.");
    }

    public static function crossCompanyAccount(string $code): self
    {
        return new self("Account {$code} does not belong to the journal's company.");
    }
}
