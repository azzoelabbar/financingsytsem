<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Gl\Prepayment;
use App\Models\Gl\PrepaymentSchedule;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Support\Decimal;
use App\Services\Gl\Exceptions\GlException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Prepayment recognition (Dr Prepaid · Cr Bank/Cash) and straight-line amortization
 * (Dr Expense · Cr Prepaid) via AccountingEngine rules.
 */
class PrepaymentService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(Company $company, AccountingBook $book, array $data): Prepayment
    {
        $amount = Decimal::of(is_scalar($data['amount'] ?? null) ? (string) $data['amount'] : '0');
        if (! Decimal::isPositive($amount)) {
            throw new PostingException('Prepayment amount must be positive.');
        }

        $periods = max(1, (int) ($data['periods'] ?? 1));
        $prepaid = is_string($data['prepaid_account_code'] ?? null) ? $data['prepaid_account_code'] : '110403';
        $expense = is_string($data['expense_account_code'] ?? null) ? $data['expense_account_code'] : '';
        $funding = is_string($data['funding_account_code'] ?? null) ? $data['funding_account_code'] : '';
        if ($expense === '' || $funding === '') {
            throw new PostingException('Prepayment requires expense_account_code and funding_account_code.');
        }
        $this->controls->assertPostingAccount($company, $prepaid);
        $this->controls->assertPostingAccount($company, $expense);
        $this->controls->assertPostingAccount($company, $funding);

        $prepaymentDate = Carbon::parse(is_string($data['prepayment_date'] ?? null) ? $data['prepayment_date'] : now()->toDateString());
        $number = isset($data['number']) ? (string) $data['number'] : null;
        if ($number !== null && Prepayment::where('company_id', $company->id)->where('number', $number)->exists()) {
            throw new GlException("Prepayment number '{$number}' already exists.");
        }

        return DB::transaction(function () use ($company, $book, $data, $amount, $periods, $prepaid, $expense, $funding, $prepaymentDate, $number): Prepayment {
            $row = Prepayment::create([
                'company_id' => $company->id,
                'book_id' => $book->id,
                'number' => $number,
                'prepayment_date' => $prepaymentDate->toDateString(),
                'prepaid_account_code' => $prepaid,
                'expense_account_code' => $expense,
                'funding_account_code' => $funding,
                'amount' => $amount,
                'periods' => $periods,
                'periods_recognized' => 0,
                'amount_recognized' => '0',
                'currency' => $data['currency'] ?? $company->functional_currency,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'created_by' => $data['created_by'] ?? null,
            ]);

            $this->buildSchedule($row, $prepaymentDate, $amount, $periods);
            $this->audit->record($row, 'created', $company->id);

            return $row->load('schedules');
        });
    }

    public function post(Prepayment $prepayment, ?User $poster = null): Prepayment
    {
        if ($prepayment->status !== 'draft') {
            throw new PostingException('Only draft prepayments can be posted.');
        }

        return DB::transaction(function () use ($prepayment, $poster): Prepayment {
            $number = $prepayment->number ?: $this->nextNumber($prepayment->company_id, Carbon::parse($prepayment->prepayment_date)->year);
            $document = new GenericSourceDocument(
                'gl.prepayment',
                Carbon::parse($prepayment->prepayment_date)->toDateString(),
                $prepayment->currency,
                $number,
                [
                    'amount' => (string) $prepayment->amount,
                    'prepaid_account' => $prepayment->prepaid_account_code,
                    'funding_account' => $prepayment->funding_account_code,
                    'book_basis' => BookBasis::LOCAL->value,
                    'exchange_rate' => (float) $prepayment->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($prepayment->company, $document, $poster);

            $prepayment->forceFill([
                'number' => $number,
                'status' => 'active',
                'journal_id' => $journal->id,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($prepayment, 'posted', $prepayment->company_id, null, [
                'journal_id' => $journal->id,
            ]);

            return $prepayment->fresh(['schedules', 'journal']) ?? $prepayment;
        });
    }

    /** @return list<PrepaymentSchedule> */
    public function recognizeDue(?Carbon $asOf = null, ?User $poster = null): array
    {
        $asOf ??= now();
        $posted = [];

        $schedules = PrepaymentSchedule::query()
            ->where('status', 'pending')
            ->whereDate('recognize_date', '<=', $asOf)
            ->whereHas('prepayment', fn ($q) => $q->where('status', 'active'))
            ->with('prepayment')
            ->orderBy('recognize_date')
            ->orderBy('period_no')
            ->get();

        foreach ($schedules as $schedule) {
            $posted[] = $this->recognize($schedule, $poster);
        }

        return $posted;
    }

    public function recognize(PrepaymentSchedule $schedule, ?User $poster = null): PrepaymentSchedule
    {
        if ($schedule->status !== 'pending') {
            throw new PostingException('Schedule line already recognized.');
        }

        return DB::transaction(function () use ($schedule, $poster): PrepaymentSchedule {
            $schedule->loadMissing('prepayment.company');
            $prepayment = $schedule->prepayment;
            if ($prepayment->status !== 'active') {
                throw new PostingException('Prepayment is not active.');
            }

            $ref = ($prepayment->number ?? 'PP').'-P'.$schedule->period_no;
            $document = new GenericSourceDocument(
                'gl.prepayment_amortization',
                Carbon::parse($schedule->recognize_date)->toDateString(),
                $prepayment->currency,
                $ref,
                [
                    'amount' => (string) $schedule->amount,
                    'expense_account' => $prepayment->expense_account_code,
                    'prepaid_account' => $prepayment->prepaid_account_code,
                    'book_basis' => BookBasis::LOCAL->value,
                    'exchange_rate' => (float) $prepayment->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($prepayment->company, $document, $poster);

            $schedule->forceFill([
                'status' => 'posted',
                'journal_id' => $journal->id,
                'posted_at' => now(),
            ])->save();

            $recognized = Decimal::add(Decimal::of($prepayment->amount_recognized ?? '0'), Decimal::of($schedule->amount));
            $periodsRecognized = (int) $prepayment->periods_recognized + 1;
            $completed = $periodsRecognized >= (int) $prepayment->periods
                || ! Decimal::isPositive(Decimal::sub(Decimal::of($prepayment->amount), $recognized));

            $prepayment->forceFill([
                'amount_recognized' => $recognized,
                'periods_recognized' => $periodsRecognized,
                'status' => $completed ? 'completed' : 'active',
            ])->save();

            $this->audit->record($schedule, 'recognized', $prepayment->company_id, null, [
                'journal_id' => $journal->id,
                'period_no' => $schedule->period_no,
            ]);

            return $schedule->fresh() ?? $schedule;
        });
    }

    /**
     * @param  numeric-string  $amount
     */
    private function buildSchedule(Prepayment $prepayment, Carbon $start, string $amount, int $periods): void
    {
        $perPeriod = Decimal::div($amount, (string) $periods);
        $allocated = '0';

        for ($i = 1; $i <= $periods; $i++) {
            $lineAmount = $i === $periods
                ? Decimal::sub($amount, $allocated)
                : $perPeriod;
            $allocated = Decimal::add($allocated, $lineAmount);

            $prepayment->schedules()->create([
                'period_no' => $i,
                'recognize_date' => $start->copy()->addMonthsNoOverflow($i - 1)->toDateString(),
                'amount' => $lineAmount,
                'status' => 'pending',
            ]);
        }
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = Prepayment::query()
            ->where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('prepayment_date', $year)
            ->count() + 1;

        return sprintf('PP-%d-%05d', $year, $seq);
    }
}
