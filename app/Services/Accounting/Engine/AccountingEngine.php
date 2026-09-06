<?php

declare(strict_types=1);

namespace App\Services\Accounting\Engine;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\DimensionValue;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\Contracts\SourceDocument;
use App\Services\Accounting\Data\JournalDraft;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;

/**
 * The single public entry point of the Accounting Engine domain (spec §10).
 * Modules reach the ledger ONLY through here: a SourceDocument is turned into a
 * balanced JournalDraft by a registered rule, which the engine validates and
 * posts through the double-entry PostingEngine (JournalService). No module ever
 * writes journal lines directly.
 */
class AccountingEngine
{
    public function __construct(
        private readonly JournalService $journals,
        private readonly RuleRegistry $rules,
    ) {}

    /**
     * Resolve the rule for the company's primary book, build a draft from the
     * document and post it.
     */
    public function postFrom(Company $company, SourceDocument $document, ?User $poster = null): Journal
    {
        $primary = $company->primaryBook()
            ?? throw new PostingException("Company {$company->code} has no primary accounting book.");
        $payload = $document->payload();
        $basis = BookBasis::tryFrom(is_string($payload['book_basis'] ?? null) ? $payload['book_basis'] : '')
            ?? $primary->basis;

        $rule = $this->rules->resolve($document->type(), $basis);
        $draft = $rule->build($document);

        return $this->post($company, $draft, $poster);
    }

    /** Post a pre-built draft into the book identified by its basis. */
    public function post(Company $company, JournalDraft $draft, ?User $poster = null): Journal
    {
        if ($draft->movements === []) {
            throw new PostingException('A journal draft must contain movements.');
        }

        $book = AccountingBook::query()
            ->where('company_id', $company->id)
            ->where('basis', $draft->book)
            ->first()
            ?? throw new PostingException("Company {$company->code} has no '{$draft->book}' book.");
        if (! $book->is_active) {
            throw new PostingException("Accounting book '{$book->code}' is closed and cannot accept postings.");
        }

        $accounts = $this->resolveAccounts($company, $draft);
        $lines = [];

        foreach ($draft->movements as $movement) {
            $account = $accounts[$movement->accountCode]
                ?? throw new PostingException("Account {$movement->accountCode} not found for company {$company->code}.");

            $lines[] = new LineInput(
                accountId: $account->id,
                debit: $movement->debit,
                credit: $movement->credit,
                description: $movement->memo,
                currency: $draft->currency,
                exchangeRate: $draft->exchangeRate,
                dimensions: $this->resolveDimensions($company, $movement->dimensions),
            );
        }

        return $this->journals->createAndPost($company, $book, [
            'journal_date' => $draft->date,
            'source' => $draft->source,
            'reference' => $draft->reference,
            'description' => $draft->description,
            'currency' => $draft->currency ?? $company->functional_currency,
            'exchange_rate' => $draft->exchangeRate,
        ], $lines, $poster);
    }

    /** @return array<string, Account> */
    private function resolveAccounts(Company $company, JournalDraft $draft): array
    {
        $codes = array_values(array_unique(array_map(
            fn ($m): string => $m->accountCode,
            $draft->movements,
        )));

        return Account::query()
            ->where('company_id', $company->id)
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code')
            ->all();
    }

    /**
     * Translate dimension codes to ids for the posting engine.
     *
     * @param  array<string, string>  $dimensions  dimension code => value code
     * @return array<int, int> dimension id => value id
     */
    private function resolveDimensions(Company $company, array $dimensions): array
    {
        $resolved = [];

        foreach ($dimensions as $dimensionCode => $valueCode) {
            $dimension = Dimension::query()
                ->where('company_id', $company->id)
                ->where('code', $dimensionCode)
                ->first()
                ?? throw new PostingException("Dimension '{$dimensionCode}' not found for company {$company->code}.");

            $value = DimensionValue::query()
                ->where('dimension_id', $dimension->id)
                ->where('code', $valueCode)
                ->first()
                ?? throw new PostingException("Dimension value '{$valueCode}' not found for dimension '{$dimensionCode}'.");

            $resolved[$dimension->id] = $value->id;
        }

        return $resolved;
    }
}
