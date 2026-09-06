<?php

declare(strict_types=1);

namespace App\Services\Accounting\Rules;

use App\Enums\Accounting\BookBasis;
use App\Services\Accounting\Contracts\AccountingRule;
use App\Services\Accounting\Contracts\SourceDocument;
use App\Services\Accounting\Data\JournalDraft;
use App\Services\Accounting\Data\LedgerMovement;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

/**
 * Generic balanced-journal rule: the source document supplies explicit movements.
 * Used by Tax/FA/Inventory/Revenue/Lease/Loan/Payroll/Cost/Consolidation modules.
 *
 * Payload: movements: [{account, debit?, credit?, memo?}], book_basis?
 */
final class PayloadMovementsRule implements AccountingRule
{
    public function __construct(
        private readonly string $documentType,
        private readonly string $journalSource,
        private readonly BookBasis $bookBasis = BookBasis::LOCAL,
    ) {}

    public function type(): string
    {
        return $this->documentType;
    }

    public function book(): BookBasis
    {
        return $this->bookBasis;
    }

    public function version(): string
    {
        return '1.0';
    }

    public function build(SourceDocument $document): JournalDraft
    {
        $payload = $document->payload();
        $book = is_string($payload['book_basis'] ?? null) ? $payload['book_basis'] : $this->bookBasis->value;
        $raw = $payload['movements'] ?? null;
        if (! is_array($raw) || count($raw) < 2) {
            throw new PostingException("Rule {$this->documentType} requires at least two movements.");
        }

        $movements = [];
        $debit = '0';
        $credit = '0';
        foreach ($raw as $i => $line) {
            if (! is_array($line)) {
                throw new PostingException("Movement {$i} is invalid.");
            }
            $account = is_string($line['account'] ?? null) ? $line['account'] : '';
            if ($account === '') {
                throw new PostingException("Movement {$i} requires account.");
            }
            $d = Decimal::of(is_scalar($line['debit'] ?? null) ? (string) $line['debit'] : '0');
            $c = Decimal::of(is_scalar($line['credit'] ?? null) ? (string) $line['credit'] : '0');
            if (Decimal::isPositive($d) === Decimal::isPositive($c)) {
                throw new PostingException("Movement {$i} must be debit XOR credit.");
            }
            $memo = is_string($line['memo'] ?? null) ? $line['memo'] : null;
            $dims = [];
            if (is_array($line['dimensions'] ?? null)) {
                foreach ($line['dimensions'] as $dimCode => $valueCode) {
                    if (is_string($dimCode) && is_string($valueCode) && $valueCode !== '') {
                        $dims[$dimCode] = $valueCode;
                    }
                }
            }
            $movements[] = Decimal::isPositive($d)
                ? LedgerMovement::debit($account, $d, $dims, $memo)
                : LedgerMovement::credit($account, $c, $dims, $memo);
            $debit = Decimal::add($debit, $d);
            $credit = Decimal::add($credit, $c);
        }

        if (! Decimal::equals($debit, $credit)) {
            throw new PostingException("Rule {$this->documentType} movements do not balance.");
        }

        return new JournalDraft(
            book: $book,
            source: $this->journalSource,
            date: $document->date(),
            reference: $document->reference(),
            description: ($this->journalSource).' '.($document->reference() ?? ''),
            movements: $movements,
            ruleVersion: $this->version(),
            currency: $document->currency(),
            exchangeRate: JournalDraft::rateFromPayload($payload),
        );
    }
}
