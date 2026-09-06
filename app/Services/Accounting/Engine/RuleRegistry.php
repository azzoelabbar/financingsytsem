<?php

declare(strict_types=1);

namespace App\Services\Accounting\Engine;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Contracts\AccountingRule;
use App\Services\Accounting\Exceptions\PostingException;

/**
 * Resolves the accounting rule for a (document type, book) pair. Registering a
 * new rule is the only way a new source transaction becomes postable — modules
 * cannot invent journals (spec §10, §48).
 */
final class RuleRegistry
{
    /** @var array<string, AccountingRule> */
    private array $rules = [];

    public function register(AccountingRule $rule): void
    {
        $this->rules[$this->key($rule->type(), $rule->book())] = $rule;
    }

    public function resolve(string $type, BookBasis $book): AccountingRule
    {
        return $this->rules[$this->key($type, $book)]
            ?? throw new PostingException("No accounting rule registered for '{$type}' on book '{$book->value}'.");
    }

    public function has(string $type, BookBasis $book): bool
    {
        return isset($this->rules[$this->key($type, $book)]);
    }

    private function key(string $type, BookBasis $book): string
    {
        return $type.'|'.$book->value;
    }
}
