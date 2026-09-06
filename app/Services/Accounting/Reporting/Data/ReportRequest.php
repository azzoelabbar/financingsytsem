<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting\Data;

/**
 * The universal report parameters every report accepts (spec: Period, Comparative,
 * Entity, Branch, Department, Cost Center, Project, Currency, Book, Framework).
 * Immutable; unset values fall back to the caller's context.
 */
final readonly class ReportRequest
{
    /**
     * @param  array<int, int>  $dimensionFilters  dimension id => value id
     */
    public function __construct(
        public int $companyId,
        public int $bookId,
        public ?string $asOf = null,           // Y-m-d; null = latest
        public ?string $comparativeAsOf = null,
        public string $currency = 'functional', // transaction|functional|presentation
        public string $framework = 'company_default',
        public string $levelOfDetail = 'account',
        public bool $includeZero = false,
        public array $dimensionFilters = [],
    ) {}
}
