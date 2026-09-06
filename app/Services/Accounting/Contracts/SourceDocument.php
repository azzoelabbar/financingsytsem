<?php

declare(strict_types=1);

namespace App\Services\Accounting\Contracts;

/**
 * A business intent raised by a module (a sale, a payroll run, a payment) — NOT
 * a journal. Modules build a SourceDocument and hand it to the AccountingEngine;
 * they never create journals directly (spec §10, §48).
 */
interface SourceDocument
{
    /** Document type, e.g. "sales.invoice", "payroll.run", "supplier.payment". */
    public function type(): string;

    /** Accounting date (Y-m-d). */
    public function date(): string;

    /** Transaction currency (ISO 4217). */
    public function currency(): string;

    /** External reference (invoice no., payroll id, …). */
    public function reference(): ?string;

    /**
     * Document data the rule needs (line amounts, tax, quantities, …).
     *
     * @return array<string, mixed>
     */
    public function payload(): array;

    /**
     * Default analytical dimensions for the document (dimension code => value code).
     *
     * @return array<string, string>
     */
    public function dimensions(): array;
}
