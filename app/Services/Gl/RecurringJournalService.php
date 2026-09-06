<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\Journal;
use App\Models\Gl\RecurringJournalTemplate;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Gl\Exceptions\GlException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Recurring journal templates → system-generated journals via JournalService.
 */
class RecurringJournalService
{
    public function __construct(
        private readonly JournalService $journals,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createTemplate(Company $company, AccountingBook $book, array $header, array $lines): RecurringJournalTemplate
    {
        if (count($lines) < 2) {
            throw new PostingException('A recurring template must have at least two lines.');
        }

        return DB::transaction(function () use ($company, $book, $header, $lines): RecurringJournalTemplate {
            $code = is_string($header['code'] ?? null) ? $header['code'] : throw new GlException('Recurring template requires a code.');
            if (RecurringJournalTemplate::where('company_id', $company->id)->where('code', $code)->exists()) {
                throw new GlException("Recurring template code '{$code}' already exists.");
            }

            $start = Carbon::parse(is_string($header['start_date'] ?? null) ? $header['start_date'] : now()->toDateString());
            $dayOfMonth = (int) ($header['day_of_month'] ?? $start->day);
            $frequency = is_string($header['frequency'] ?? null) ? $header['frequency'] : 'monthly';
            if (! in_array($frequency, ['monthly', 'quarterly', 'yearly'], true)) {
                throw new GlException("Unsupported recurring frequency '{$frequency}'.");
            }

            $template = RecurringJournalTemplate::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'code' => $code,
                'name' => is_string($header['name'] ?? null) ? $header['name'] : $code,
                'frequency' => $frequency,
                'day_of_month' => max(1, min(28, $dayOfMonth)),
                'start_date' => $start->toDateString(),
                'end_date' => $header['end_date'] ?? null,
                'next_run_date' => $header['next_run_date'] ?? $start->toDateString(),
                'currency' => $header['currency'] ?? $company->functional_currency,
                'exchange_rate' => $header['exchange_rate'] ?? 1,
                'description' => $header['description'] ?? null,
                'is_active' => (bool) ($header['is_active'] ?? true),
            ]);

            $totalDebit = '0';
            $totalCredit = '0';
            $lineNo = 0;
            foreach ($lines as $line) {
                $lineNo++;
                $accountCode = is_string($line['account_code'] ?? null) ? $line['account_code'] : '';
                if ($accountCode === '') {
                    throw new PostingException("Recurring line {$lineNo} requires account_code.");
                }
                $this->assertAccount($company, $accountCode);

                $debit = Decimal::of(is_scalar($line['debit'] ?? null) ? (string) $line['debit'] : '0');
                $credit = Decimal::of(is_scalar($line['credit'] ?? null) ? (string) $line['credit'] : '0');
                if (Decimal::isPositive($debit) === Decimal::isPositive($credit)) {
                    throw new PostingException("Recurring line {$lineNo} must be debit XOR credit.");
                }

                $template->lines()->create([
                    'line_no' => $lineNo,
                    'account_code' => $accountCode,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $line['description'] ?? null,
                    'dimensions' => is_array($line['dimensions'] ?? null) ? $line['dimensions'] : null,
                ]);
                $totalDebit = Decimal::add($totalDebit, $debit);
                $totalCredit = Decimal::add($totalCredit, $credit);
            }

            if (! Decimal::equals($totalDebit, $totalCredit)) {
                throw new PostingException('Recurring template lines must balance.');
            }

            $this->audit->record($template, 'created', $company->id);

            return $template->load('lines');
        });
    }

    /** @return list<Journal> */
    public function runDue(Company $company, ?Carbon $asOf = null, ?User $poster = null): array
    {
        $asOf ??= now();
        $posted = [];

        $templates = RecurringJournalTemplate::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->whereNotNull('next_run_date')
            ->whereDate('next_run_date', '<=', $asOf)
            ->with('lines', 'book')
            ->get();

        foreach ($templates as $template) {
            while (
                $template->is_active
                && $template->next_run_date !== null
                && $template->next_run_date->lte($asOf)
                && ($template->end_date === null || $template->next_run_date->lte($template->end_date))
            ) {
                $posted[] = $this->runOnce($template, $poster);
                $template->refresh();
            }
        }

        return $posted;
    }

    public function runOnce(RecurringJournalTemplate $template, ?User $poster = null): Journal
    {
        return DB::transaction(function () use ($template, $poster): Journal {
            $template->loadMissing('lines', 'book', 'company');
            $runDate = Carbon::parse($template->next_run_date ?? $template->start_date);

            $accounts = Account::query()
                ->where('company_id', $template->company_id)
                ->whereIn('code', $template->lines->pluck('account_code')->all())
                ->get()
                ->keyBy('code');

            $lines = [];
            foreach ($template->lines as $line) {
                $account = $accounts[$line->account_code]
                    ?? throw new PostingException("Account {$line->account_code} not found.");
                $lines[] = new LineInput(
                    accountId: $account->id,
                    debit: $line->debit,
                    credit: $line->credit,
                    description: $line->description,
                );
            }

            $journal = $this->journals->createAndPost($template->company, $template->book, [
                'journal_date' => $runDate->toDateString(),
                'source' => 'recurring',
                'reference' => $template->code,
                'description' => $template->description ?? $template->name,
                'currency' => $template->currency,
                'exchange_rate' => (float) $template->exchange_rate,
                'is_system_generated' => true,
                'created_by' => $poster?->id,
            ], $lines, $poster);

            $next = $this->advanceDate($runDate, $template->frequency, (int) $template->day_of_month);
            $stillActive = $template->end_date === null || $next->lte($template->end_date);

            $template->forceFill([
                'next_run_date' => $stillActive ? $next->toDateString() : null,
                'is_active' => $stillActive,
            ])->save();

            $this->audit->record($template, 'run', $template->company_id, null, [
                'journal_id' => $journal->id,
                'run_date' => $runDate->toDateString(),
                'next_run_date' => $template->next_run_date,
            ]);

            return $journal;
        });
    }

    private function advanceDate(Carbon $from, string $frequency, int $dayOfMonth): Carbon
    {
        $next = match ($frequency) {
            'quarterly' => $from->copy()->addMonthsNoOverflow(3),
            'yearly' => $from->copy()->addYearNoOverflow(),
            default => $from->copy()->addMonthNoOverflow(),
        };

        return $next->day(min($dayOfMonth, $next->daysInMonth));
    }

    private function assertAccount(Company $company, string $code): void
    {
        $account = Account::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->first();

        if ($account === null || ! $account->canPost()) {
            throw new PostingException("Account {$code} is not a posting account on the chart.");
        }
    }
}
