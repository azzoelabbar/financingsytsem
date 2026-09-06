<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Gl\OpeningBalanceBatch;
use App\Models\Gl\OpeningBalanceRun;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\EnginePoster;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use Illuminate\Support\Facades\DB;

class OpeningBalanceService
{
    public function __construct(
        private readonly EnginePoster $poster,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    public function createDraft(Company $company, AccountingBook $book, string $asOf, ?string $currency = null): OpeningBalanceBatch
    {
        if (OpeningBalanceBatch::query()->where('company_id', $company->id)->where('book_id', $book->id)->whereDate('as_of', $asOf)->exists()) {
            throw new PostingException('Duplicate opening balance batch for this company/book/date.');
        }

        $batch = OpeningBalanceBatch::create([
            'company_id' => $company->id,
            'book_id' => $book->id,
            'as_of' => $asOf,
            'status' => 'draft',
            'currency' => $currency ?? (string) $company->functional_currency,
        ]);
        $this->audit->record($batch, 'created', $company->id);

        return $batch;
    }

    /**
     * @param  array{account: string, debit?: string|float|int, credit?: string|float|int, currency?: string, exchange_rate?: float|int|string, dimensions?: array<string, string>}  $line
     */
    public function addLine(OpeningBalanceBatch $batch, array $line): OpeningBalanceBatch
    {
        if ($batch->status !== 'draft') {
            throw new PostingException('Only draft opening batches accept lines.');
        }
        $code = $line['account'];
        $account = $this->controls->assertPostingAccount($batch->company, $code);
        if (! $account->opening_balance_allowed || $account->account_type->isTemporary()) {
            throw new PostingException("Account {$code} does not allow opening balances (temporary/P&L).");
        }
        $d = Decimal::of(is_scalar($line['debit'] ?? null) ? (string) $line['debit'] : '0');
        $c = Decimal::of(is_scalar($line['credit'] ?? null) ? (string) $line['credit'] : '0');
        if (Decimal::isPositive($d) === Decimal::isPositive($c)) {
            throw new PostingException('Opening line must be debit XOR credit.');
        }
        $batch->lines()->create([
            'account_code' => $code,
            'debit' => $d,
            'credit' => $c,
            'currency' => $line['currency'] ?? $batch->currency,
            'exchange_rate' => is_numeric($line['exchange_rate'] ?? null) ? (float) $line['exchange_rate'] : 1.0,
            'dimensions' => is_array($line['dimensions'] ?? null) ? $line['dimensions'] : null,
        ]);

        return $batch->fresh(['lines']) ?? $batch;
    }

    public function validate(OpeningBalanceBatch $batch): OpeningBalanceBatch
    {
        $batch->loadMissing('lines');
        if ($batch->lines->count() < 2) {
            throw new PostingException('Opening balances require at least two movements.');
        }
        $debit = '0';
        $credit = '0';
        foreach ($batch->lines as $line) {
            $debit = Decimal::add($debit, Decimal::of($line->debit));
            $credit = Decimal::add($credit, Decimal::of($line->credit));
        }
        if (! Decimal::equals($debit, $credit)) {
            throw new PostingException('Opening balance batch is not balanced.');
        }
        $batch->forceFill(['status' => 'validated'])->save();
        $this->audit->record($batch, 'submitted', $batch->company_id);

        return $batch;
    }

    public function postBatch(OpeningBalanceBatch $batch, ?BookBasis $basis = null): OpeningBalanceBatch
    {
        if ($batch->status === 'locked') {
            throw new PostingException('Opening balance batch is locked.');
        }
        if ($batch->status === 'posted') {
            throw new PostingException('Opening balance batch is already posted.');
        }
        if ($batch->status === 'draft') {
            $this->validate($batch);
        }
        $batch->loadMissing('lines', 'company', 'book');
        $basis ??= $batch->book->basis;
        $movements = [];
        foreach ($batch->lines as $line) {
            $d = Decimal::of($line->debit);
            $c = Decimal::of($line->credit);
            $movements[] = Decimal::isPositive($d)
                ? ['account' => $line->account_code, 'debit' => $d, 'memo' => 'Opening balance', 'dimensions' => is_array($line->dimensions) ? $line->dimensions : []]
                : ['account' => $line->account_code, 'credit' => $c, 'memo' => 'Opening balance', 'dimensions' => is_array($line->dimensions) ? $line->dimensions : []];
        }

        return DB::transaction(function () use ($batch, $movements, $basis): OpeningBalanceBatch {
            $journal = $this->poster->post(
                $batch->company,
                'opening.balance',
                $batch->as_of->toDateString(),
                $batch->currency,
                'OB-'.$batch->as_of->toDateString(),
                $movements,
                book: $basis,
            );
            $batch->forceFill(['status' => 'posted', 'journal_id' => $journal->id])->save();
            OpeningBalanceRun::create([
                'company_id' => $batch->company_id,
                'book_id' => $batch->book_id,
                'as_of' => $batch->as_of,
                'journal_id' => $journal->id,
            ]);
            $this->audit->record($batch, 'posted', $batch->company_id, null, ['journal_id' => $journal->id]);

            return $batch->fresh(['lines', 'journal']) ?? $batch;
        });
    }

    public function lock(OpeningBalanceBatch $batch): OpeningBalanceBatch
    {
        if ($batch->status !== 'posted') {
            throw new PostingException('Only posted opening batches can be locked.');
        }
        $batch->forceFill(['status' => 'locked'])->save();
        $this->audit->record($batch, 'closed', $batch->company_id);

        return $batch;
    }

    /**
     * @param  list<array{account: string, debit?: string|float|int, credit?: string|float|int}>  $lines
     */
    public function post(
        Company $company,
        AccountingBook $book,
        string $asOf,
        array $lines,
        BookBasis $basis = BookBasis::LOCAL,
    ): OpeningBalanceRun {
        $batch = $this->createDraft($company, $book, $asOf);
        foreach ($lines as $line) {
            $this->addLine($batch, $line);
        }
        $posted = $this->postBatch($batch->fresh() ?? $batch, $basis);

        return OpeningBalanceRun::query()->where('journal_id', $posted->journal_id)->firstOrFail();
    }
}
