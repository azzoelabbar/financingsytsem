<?php

declare(strict_types=1);

namespace App\Services\Gl;

use App\Enums\Accounting\BookBasis;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Gl\Accrual;
use App\Models\User;
use App\Services\Accounting\AuditLogger;
use App\Services\Accounting\ControlAccountResolver;
use App\Services\Accounting\Data\GenericSourceDocument;
use App\Services\Accounting\Engine\AccountingEngine;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Support\Decimal;
use App\Services\Gl\Exceptions\GlException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Period accruals: Dr Expense · Cr Accrued Liability, with optional auto-reversal
 * on the first day of the next fiscal period.
 */
class AccrualService
{
    public function __construct(
        private readonly AccountingEngine $engine,
        private readonly JournalService $journals,
        private readonly PeriodService $periods,
        private readonly ControlAccountResolver $controls,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(Company $company, AccountingBook $book, array $data): Accrual
    {
        $amount = Decimal::of(is_scalar($data['amount'] ?? null) ? (string) $data['amount'] : '0');
        if (! Decimal::isPositive($amount)) {
            throw new PostingException('Accrual amount must be positive.');
        }

        $expense = is_string($data['expense_account_code'] ?? null) ? $data['expense_account_code'] : '';
        $accrual = is_string($data['accrual_account_code'] ?? null) ? $data['accrual_account_code'] : '210203';
        $this->controls->assertPostingAccount($company, $expense);
        $this->controls->assertPostingAccount($company, $accrual);

        $accrualDate = Carbon::parse(is_string($data['accrual_date'] ?? null) ? $data['accrual_date'] : now()->toDateString());
        $autoReverse = (bool) ($data['auto_reverse'] ?? true);
        $reversalDate = isset($data['reversal_date'])
            ? Carbon::parse((string) $data['reversal_date'])->toDateString()
            : ($autoReverse ? $this->defaultReversalDate($company, $accrualDate) : null);

        $number = isset($data['number']) ? (string) $data['number'] : null;
        if ($number !== null && Accrual::where('company_id', $company->id)->where('number', $number)->exists()) {
            throw new GlException("Accrual number '{$number}' already exists.");
        }

        $row = Accrual::create([
            'company_id' => $company->id,
            'book_id' => $book->id,
            'number' => $number,
            'accrual_date' => $accrualDate->toDateString(),
            'reversal_date' => $reversalDate,
            'expense_account_code' => $expense,
            'accrual_account_code' => $accrual,
            'amount' => $amount,
            'currency' => $data['currency'] ?? $company->functional_currency,
            'exchange_rate' => $data['exchange_rate'] ?? 1,
            'description' => $data['description'] ?? null,
            'status' => 'draft',
            'auto_reverse' => $autoReverse,
            'created_by' => $data['created_by'] ?? null,
        ]);

        $this->audit->record($row, 'created', $company->id);

        return $row;
    }

    public function post(Accrual $accrual, ?User $poster = null): Accrual
    {
        if ($accrual->status !== 'draft') {
            throw new PostingException('Only draft accruals can be posted.');
        }

        return DB::transaction(function () use ($accrual, $poster): Accrual {
            $number = $accrual->number ?: $this->nextNumber($accrual->company_id, Carbon::parse($accrual->accrual_date)->year);
            $document = new GenericSourceDocument(
                'gl.accrual',
                Carbon::parse($accrual->accrual_date)->toDateString(),
                $accrual->currency,
                $number,
                [
                    'amount' => (string) $accrual->amount,
                    'expense_account' => $accrual->expense_account_code,
                    'accrual_account' => $accrual->accrual_account_code,
                    'book_basis' => BookBasis::LOCAL->value,
                    'exchange_rate' => (float) $accrual->exchange_rate,
                ],
            );

            $journal = $this->engine->postFrom($accrual->company, $document, $poster);

            $accrual->forceFill([
                'number' => $number,
                'status' => 'posted',
                'journal_id' => $journal->id,
                'posted_at' => now(),
            ])->save();

            $this->audit->record($accrual, 'posted', $accrual->company_id, null, [
                'journal_id' => $journal->id,
            ]);

            return $accrual->fresh(['journal']) ?? $accrual;
        });
    }

    /** @return list<Accrual> */
    public function reverseDue(?Carbon $asOf = null, ?User $actor = null): array
    {
        $asOf ??= now();
        $reversed = [];

        $rows = Accrual::query()
            ->where('status', 'posted')
            ->where('auto_reverse', true)
            ->whereNotNull('reversal_date')
            ->whereDate('reversal_date', '<=', $asOf)
            ->whereNull('reversal_journal_id')
            ->with('journal')
            ->get();

        foreach ($rows as $row) {
            $reversed[] = $this->reverse($row, $actor);
        }

        return $reversed;
    }

    public function reverse(Accrual $accrual, ?User $actor = null): Accrual
    {
        if ($accrual->status !== 'posted' || $accrual->journal_id === null) {
            throw new PostingException('Only posted accruals can be reversed.');
        }
        if ($accrual->reversal_journal_id !== null) {
            throw new PostingException('Accrual already reversed.');
        }

        return DB::transaction(function () use ($accrual, $actor): Accrual {
            $accrual->loadMissing('journal');
            $date = $accrual->reversal_date !== null
                ? Carbon::parse($accrual->reversal_date)
                : now();

            $reversal = $this->journals->reverse(
                $accrual->journal,
                $actor,
                $date,
                'Auto-reverse accrual '.($accrual->number ?? (string) $accrual->id),
            );

            $accrual->forceFill([
                'status' => 'reversed',
                'reversal_journal_id' => $reversal->id,
            ])->save();

            $this->audit->record($accrual, 'reversed', $accrual->company_id, null, [
                'reversal_journal_id' => $reversal->id,
            ]);

            return $accrual->fresh() ?? $accrual;
        });
    }

    private function defaultReversalDate(Company $company, Carbon $accrualDate): string
    {
        $period = $this->periods->forDate($company, $accrualDate);
        if ($period === null) {
            return $accrualDate->copy()->addMonthNoOverflow()->startOfMonth()->toDateString();
        }

        return Carbon::parse($period->end_date)->addDay()->toDateString();
    }

    private function nextNumber(int $companyId, int $year): string
    {
        $seq = Accrual::query()
            ->where('company_id', $companyId)
            ->whereNotNull('number')
            ->whereYear('accrual_date', $year)
            ->count() + 1;

        return sprintf('ACR-%d-%05d', $year, $seq);
    }
}
