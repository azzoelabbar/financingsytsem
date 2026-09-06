<?php

declare(strict_types=1);

namespace App\Services\Accounting\Contracts;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Data\JournalDraft;

/**
 * Pure, versioned, book-aware translation of a business event into a balanced
 * journal draft (spec §48). A rule contains ONLY accounting logic: it reads the
 * source document and returns movements. It never touches the database and never
 * posts — the AccountingEngine validates and posts the draft it returns.
 */
interface AccountingRule
{
    /** The SourceDocument type this rule handles (e.g. "sales.invoice"). */
    public function type(): string;

    /** The book this rule produces treatment for (LOCAL / IFRS / TAX). */
    public function book(): BookBasis;

    /** Rule version, stamped onto every journal it produces (spec §49). */
    public function version(): string;

    /** Build a balanced draft from the document (or throw if it cannot balance). */
    public function build(SourceDocument $document): JournalDraft;
}
