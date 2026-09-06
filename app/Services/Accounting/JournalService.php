<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\JournalStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\User;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Exceptions\UnbalancedJournalException;
use App\Services\Accounting\Support\Decimal;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The double-entry engine (spec §9, §10, §31). This is the ONLY sanctioned way
 * to create and post journals. It guarantees the core invariants:
 *   - every journal balances in the functional currency;
 *   - postings hit posting-enabled, active, in-company accounts only;
 *   - mandatory analytical dimensions are present;
 *   - posting respects the fiscal-period lock;
 *   - posted journals are immutable and are corrected by reversal, never delete.
 */
class JournalService
{
    public function __construct(
        private readonly PeriodService $periods,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Build and validate a DRAFT journal (not yet posted). Structural integrity
     * (balance, account rules, dimensions) is enforced here; the period lock is
     * enforced at post time.
     *
     * @param  array<string, mixed>  $header
     * @param  array<int, LineInput>  $lines
     */
    public function createDraft(Company $company, AccountingBook $book, array $header, array $lines): Journal
    {
        if (count($lines) < 2) {
            throw PostingException::emptyJournal();
        }

        return DB::transaction(function () use ($company, $book, $header, $lines): Journal {
            $journalCurrency = $header['currency'] ?? $company->functional_currency;
            $journalRate = (float) ($header['exchange_rate'] ?? 1);
            $manual = ($header['source'] ?? 'manual') === 'manual' && ! ($header['is_system_generated'] ?? false);

            $journal = new Journal([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'journal_date' => $header['journal_date'] ?? now()->toDateString(),
                'document_date' => $header['document_date'] ?? null,
                'source' => $header['source'] ?? 'manual',
                'reference' => $header['reference'] ?? null,
                'description' => $header['description'] ?? null,
                'currency' => $journalCurrency,
                'exchange_rate' => $journalRate,
                'status' => JournalStatus::DRAFT,
                'is_system_generated' => (bool) ($header['is_system_generated'] ?? false),
                'created_by' => $header['created_by'] ?? Auth::id(),
            ]);
            $journal->save();

            $accounts = $this->loadAccounts($company, $lines);
            $totalDebit = '0';
            $totalCredit = '0';
            $lineNo = 0;

            foreach ($lines as $input) {
                $lineNo++;
                $account = $accounts[$input->accountId]
                    ?? throw PostingException::crossCompanyAccount((string) $input->accountId);

                $this->assertPostable($account, $manual);

                $debit = Decimal::of($input->debit);
                $credit = Decimal::of($input->credit);
                $this->assertDebitXorCredit($debit, $credit, $lineNo);

                $lineCurrency = $input->currency ?? $journalCurrency;
                $lineRate = Decimal::of($input->exchangeRate ?? $journalRate);
                $fnDebit = Decimal::mul($debit, $lineRate);
                $fnCredit = Decimal::mul($credit, $lineRate);

                $line = new JournalLine([
                    'journal_id' => $journal->id,
                    'account_id' => $account->id,
                    'line_no' => $lineNo,
                    'description' => $input->description,
                    'currency' => $lineCurrency,
                    'exchange_rate' => $lineRate,
                    'debit' => $debit,
                    'credit' => $credit,
                    'functional_debit' => $fnDebit,
                    'functional_credit' => $fnCredit,
                ]);
                $line->save();

                $this->attachDimensions($line, $account, $input);

                $totalDebit = Decimal::add($totalDebit, $fnDebit);
                $totalCredit = Decimal::add($totalCredit, $fnCredit);
            }

            if (! Decimal::equals($totalDebit, $totalCredit)) {
                throw UnbalancedJournalException::make($totalDebit, $totalCredit);
            }

            $journal->forceFill([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ])->save();

            $this->audit->record($journal, 'created', $company->id, null, [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'lines' => $lineNo,
            ]);

            return $journal->load('lines');
        });
    }

    /** Post a draft/approved journal into the ledger, enforcing the period lock. */
    public function post(Journal $journal, ?User $poster = null, bool $allowSoftClosed = false): Journal
    {
        if (! $journal->status->isMutable()) {
            throw PostingException::alreadyPosted();
        }

        return DB::transaction(function () use ($journal, $poster, $allowSoftClosed): Journal {
            $company = $journal->company;
            $postingDate = Carbon::parse($journal->posting_date ?? $journal->journal_date);
            $period = $this->periods->resolvePostable($company, $postingDate, $allowSoftClosed);

            // Defensive re-check of the balance invariant before it becomes immutable.
            $debit = Decimal::of($journal->total_debit);
            $credit = Decimal::of($journal->total_credit);
            if (! Decimal::equals($debit, $credit)) {
                throw UnbalancedJournalException::make($debit, $credit);
            }

            $journal->forceFill([
                'status' => JournalStatus::POSTED,
                'fiscal_period_id' => $period->id,
                'posting_date' => $postingDate->toDateString(),
                'number' => $journal->number ?: $this->nextNumber($journal),
                'posted_by' => $poster instanceof User ? $poster->id : Auth::id(),
                'posted_at' => now(),
            ])->save();

            $this->audit->record($journal, 'posted', $journal->company_id, null, [
                'number' => $journal->number,
                'period_id' => $period->id,
            ]);

            return $journal;
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, LineInput>  $lines
     */
    public function createAndPost(Company $company, AccountingBook $book, array $header, array $lines, ?User $poster = null): Journal
    {
        $journal = $this->createDraft($company, $book, $header, $lines);

        return $this->post($journal, $poster);
    }

    /**
     * Reverse a posted journal by generating a mirror entry (spec §31). The
     * original is never modified except to link its reversal.
     */
    public function reverse(Journal $journal, ?User $user = null, ?CarbonInterface $date = null, ?string $reason = null): Journal
    {
        if (! $journal->status->isPosted()) {
            throw PostingException::notPosted();
        }

        if ($journal->reversed_by_journal_id !== null) {
            throw PostingException::alreadyReversed();
        }

        return DB::transaction(function () use ($journal, $user, $date, $reason): Journal {
            $journal->loadMissing('lines');
            $reversalDate = $date !== null ? Carbon::parse($date->toDateString()) : now();

            $lines = $journal->lines->map(fn (JournalLine $line): LineInput => new LineInput(
                accountId: $line->account_id,
                debit: $line->credit,   // mirror
                credit: $line->debit,
                description: $line->description,
                currency: $line->currency,
                exchangeRate: (float) $line->exchange_rate,
                dimensions: $line->dimensions()->pluck('dimension_value_id', 'dimension_id')->all(),
            ))->all();

            $reversal = $this->createDraft($journal->company, $journal->book, [
                'journal_date' => $reversalDate->toDateString(),
                'posting_date' => $reversalDate->toDateString(),
                'source' => 'reversal',
                'reference' => $journal->number,
                'description' => 'عكس القيد '.$journal->number.($reason ? ' — '.$reason : ''),
                'currency' => $journal->currency,
                'exchange_rate' => (float) $journal->exchange_rate,
                'is_system_generated' => true,
                'created_by' => $user?->id,
            ], $lines);

            $reversal->forceFill(['reversal_of_journal_id' => $journal->id])->save();
            $this->post($reversal, $user);

            $journal->forceFill([
                'status' => JournalStatus::REVERSED,
                'reversed_by_journal_id' => $reversal->id,
                'reversed_at' => now(),
            ])->save();

            $this->audit->record($journal, 'reversed', $journal->company_id, null, [
                'reversal_journal_id' => $reversal->id,
                'reason' => $reason,
            ], $reason);

            return $reversal;
        });
    }

    // --- internals -------------------------------------------------------

    /**
     * @param  array<int, LineInput>  $lines
     * @return array<int, Account>
     */
    private function loadAccounts(Company $company, array $lines): array
    {
        $ids = array_unique(array_map(fn (LineInput $l) => $l->accountId, $lines));

        return Account::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }

    private function assertPostable(Account $account, bool $manual): void
    {
        if (! $account->is_active) {
            throw PostingException::accountInactive($account->code);
        }

        if (! $account->canPost()) {
            throw PostingException::nonPostingAccount($account->code);
        }

        if ($manual && ! $account->allowsManualPosting()) {
            throw PostingException::manualNotAllowed($account->code);
        }
    }

    /**
     * @param  numeric-string  $debit
     * @param  numeric-string  $credit
     */
    private function assertDebitXorCredit(string $debit, string $credit, int $lineNo): void
    {
        if (Decimal::isNegative($debit) || Decimal::isNegative($credit)) {
            throw PostingException::lineNotDebitXorCredit($lineNo);
        }

        // Exactly one side must be positive.
        if (Decimal::isPositive($debit) === Decimal::isPositive($credit)) {
            throw PostingException::lineNotDebitXorCredit($lineNo);
        }
    }

    private function attachDimensions(JournalLine $line, Account $account, LineInput $input): void
    {
        $required = [];
        if ($account->requires_cost_center) {
            $required[] = 'COST_CENTER';
        }
        if ($account->requires_project) {
            $required[] = 'PROJECT';
        }
        if ($account->requires_branch) {
            $required[] = 'BRANCH';
        }
        if ($account->requires_department) {
            $required[] = 'DEPARTMENT';
        }

        $providedDimensionCodes = [];
        foreach ($input->dimensions as $dimensionId => $valueId) {
            $line->dimensions()->create([
                'dimension_id' => $dimensionId,
                'dimension_value_id' => $valueId,
            ]);
        }

        if ($required !== []) {
            $providedDimensionCodes = Dimension::query()
                ->whereIn('id', array_keys($input->dimensions))
                ->pluck('code')
                ->all();

            foreach ($required as $code) {
                if (! in_array($code, $providedDimensionCodes, true)) {
                    throw PostingException::missingDimension($account->code, $code);
                }
            }
        }
    }

    private function nextNumber(Journal $journal): string
    {
        $book = $journal->book;
        $year = Carbon::parse($journal->posting_date ?? $journal->journal_date)->year;

        $seq = Journal::query()
            ->where('company_id', $journal->company_id)
            ->where('book_id', $journal->book_id)
            ->whereNotNull('number')
            ->whereYear('journal_date', $year)
            ->count() + 1;

        return sprintf('%s-%d-%05d', $book->code, $year, $seq);
    }
}
