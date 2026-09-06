<?php

declare(strict_types=1);

namespace App\Services\Accounting\Data;

use App\Services\Accounting\Contracts\SourceDocument;

/**
 * A simple concrete SourceDocument built from arrays. Modules will normally
 * implement their own typed documents; this is used for lightweight callers and
 * tests.
 */
final readonly class GenericSourceDocument implements SourceDocument
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $dimensions
     */
    public function __construct(
        private string $type,
        private string $date,
        private string $currency,
        private ?string $reference,
        private array $payload,
        private array $dimensions = [],
    ) {}

    public function type(): string
    {
        return $this->type;
    }

    public function date(): string
    {
        return $this->date;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function reference(): ?string
    {
        return $this->reference;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function dimensions(): array
    {
        return $this->dimensions;
    }
}
