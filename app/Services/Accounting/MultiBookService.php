<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\BookAccountMapping;
use App\Models\Accounting\BookPostingRule;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\User;
use App\Services\Accounting\Data\TrialBalanceRow;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;

class MultiBookService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly TrialBalanceService $trialBalance,
        private readonly ControlAccountResolver $controls,
    ) {}

    public function mapAccount(Company $company, AccountingBook $book, string $source, string $target): BookAccountMapping
    {
        $this->controls->assertPostingAccount($company, $source);
        $this->controls->assertPostingAccount($company, $target);

        return BookAccountMapping::query()->updateOrCreate(
            ['book_id' => $book->id, 'source_account_code' => $source],
            ['company_id' => $company->id, 'target_account_code' => $target],
        );
    }

    public function setPostingRule(Company $company, AccountingBook $book, string $sourceType, string|float|int $factor = 1): BookPostingRule
    {
        return BookPostingRule::query()->updateOrCreate(
            ['book_id' => $book->id, 'source_type' => $sourceType],
            ['company_id' => $company->id, 'amount_factor' => Decimal::of($factor), 'is_active' => true],
        );
    }

    /**
     * @param  list<array{account: string, debit?: string|float|int, credit?: string|float|int, memo?: string, dimensions?: array<string, string>}>  $movements
     * @param  list<BookBasis>  $books
     * @return list<Journal>
     */
    public function postParallel(
        Company $company,
        string $type,
        string $date,
        string $currency,
        ?string $reference,
        array $movements,
        array $books = [BookBasis::LOCAL, BookBasis::IFRS],
        ?User $poster = null,
    ): array {
        $journals = [];
        foreach ($books as $basis) {
            $book = AccountingBook::query()
                ->where('company_id', $company->id)
                ->where('basis', $basis)
                ->first()
                ?? throw new PostingException("Missing book '{$basis->value}'.");
            if (! $book->is_active) {
                throw new PostingException("Accounting book '{$book->code}' is closed and cannot accept postings.");
            }
            $mapped = $this->applyBookTreatment($company, $book, $type, $movements);
            $journals[] = $this->poster->post(
                $company,
                $type,
                $date,
                $currency,
                $reference,
                $mapped,
                poster: $poster,
                book: $basis,
            );
        }

        return $journals;
    }

    /**
     * @return array{matched: bool, differences: list<array{code: string, left: numeric-string, right: numeric-string, reason: string}>}
     */
    public function reconcile(Company $company, AccountingBook $left, AccountingBook $right): array
    {
        $a = $this->trialBalance->build($company, $left)->keyBy('code');
        $b = $this->trialBalance->build($company, $right)->keyBy('code');
        $codes = array_values(array_unique(array_merge($a->keys()->all(), $b->keys()->all())));
        $differences = [];
        foreach ($codes as $code) {
            $leftRow = $a->get($code);
            $rightRow = $b->get($code);
            $leftBal = $leftRow instanceof TrialBalanceRow ? $leftRow->balance : Decimal::of('0');
            $rightBal = $rightRow instanceof TrialBalanceRow ? $rightRow->balance : Decimal::of('0');
            if (! Decimal::equals($leftBal, $rightBal)) {
                $differences[] = [
                    'code' => (string) $code,
                    'left' => $leftBal,
                    'right' => $rightBal,
                    'reason' => 'book_balance_mismatch',
                ];
            }
        }

        return ['matched' => $differences === [], 'differences' => $differences];
    }

    /**
     * @param  list<array{account: string, debit?: string|float|int, credit?: string|float|int, memo?: string, dimensions?: array<string, string>}>  $movements
     * @return list<array{account: string, debit?: string, credit?: string, memo?: string, dimensions?: array<string, string>}>
     */
    private function applyBookTreatment(Company $company, AccountingBook $book, string $type, array $movements): array
    {
        $factorRule = BookPostingRule::query()
            ->where('book_id', $book->id)
            ->where('source_type', $type)
            ->where('is_active', true)
            ->first();
        $factor = $factorRule !== null ? Decimal::of((string) $factorRule->amount_factor) : Decimal::of('1');
        $out = [];
        foreach ($movements as $line) {
            $source = $line['account'];
            $map = BookAccountMapping::query()
                ->where('book_id', $book->id)
                ->where('source_account_code', $source)
                ->first();
            $target = $map instanceof BookAccountMapping ? $map->target_account_code : $source;
            $this->controls->assertPostingAccount($company, $target);
            $debit = Decimal::of(is_scalar($line['debit'] ?? null) ? (string) $line['debit'] : '0');
            $credit = Decimal::of(is_scalar($line['credit'] ?? null) ? (string) $line['credit'] : '0');
            $row = [
                'account' => $target,
                'memo' => $line['memo'] ?? null,
                'dimensions' => $line['dimensions'] ?? [],
            ];
            if (Decimal::isPositive($debit)) {
                $row['debit'] = Decimal::mul($debit, $factor);
            } else {
                $row['credit'] = Decimal::mul($credit, $factor);
            }
            $out[] = $row;
        }

        return $out;
    }
}
