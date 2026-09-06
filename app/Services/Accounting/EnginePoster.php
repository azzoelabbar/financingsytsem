<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;

/**
 * Posts caller-built balanced movements through the Accounting Engine.
 *
 * @phpstan-type Movement array{account: string, debit?: string|float|int, credit?: string|float|int, memo?: string, dimensions?: array<string, string>}
 */
final class EnginePoster
{
    public function __construct(
        private readonly AccountingEngine $engine,
    ) {}

    /**
     * @param  list<Movement>  $movements
     */
    public function post(
        Company $company,
        string $type,
        string $date,
        string $currency,
        ?string $reference,
        array $movements,
        float $exchangeRate = 1.0,
        ?User $poster = null,
        BookBasis $book = BookBasis::LOCAL,
    ): Journal {
        $document = new GenericSourceDocument(
            $type,
            $date,
            $currency,
            $reference,
            [
                'movements' => $movements,
                'book_basis' => $book->value,
                'exchange_rate' => $exchangeRate,
            ],
        );

        return $this->engine->postFrom($company, $document, $poster);
    }
}
