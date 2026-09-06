<?php

declare(strict_types=1);

namespace App\Application\Api\Gl;

use App\Application\Api\ApiQuery;
use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\User;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Gl\ManualJournalService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class GlApplicationService
{
    public function __construct(
        private readonly ManualJournalService $journals,
        private readonly TrialBalanceService $trialBalance,
        private readonly FinancialStatementService $statements,
        private readonly PeriodService $periods,
    ) {}

    /** @return LengthAwarePaginator<int, Account> */
    public function listAccounts(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Account::query()->where('company_id', $company->id),
            $request,
            ['id', 'code', 'name_ar', 'account_type', 'created_at'],
            ['code' => 'code', 'account_type' => 'account_type', 'is_posting' => 'is_posting'],
            'code',
        );
    }

    public function findAccount(Company $company, int $id): Account
    {
        return Account::query()->where('company_id', $company->id)->where('id', $id)->firstOrFail();
    }

    /** @return LengthAwarePaginator<int, Journal> */
    public function listJournals(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            Journal::query()->where('company_id', $company->id)->where('book_id', $book->id)->with('lines'),
            $request,
            ['id', 'number', 'journal_date', 'status', 'created_at'],
            ['status' => 'status', 'source' => 'source'],
            'journal_date',
        );
    }

    public function findJournal(Company $company, AccountingBook $book, int $id): Journal
    {
        return Journal::query()
            ->where('company_id', $company->id)
            ->where('book_id', $book->id)
            ->where('id', $id)
            ->with('lines')
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array{account: string, debit?: numeric, credit?: numeric, description?: string}>  $lines
     */
    public function createJournalDraft(Company $company, AccountingBook $book, array $header, array $lines): Journal
    {
        $inputs = [];
        foreach ($lines as $line) {
            $account = Account::query()
                ->where('company_id', $company->id)
                ->where('code', $line['account'])
                ->firstOrFail();
            $debit = $line['debit'] ?? 0;
            $credit = $line['credit'] ?? 0;
            $inputs[] = new LineInput(
                accountId: $account->id,
                debit: $debit,
                credit: $credit,
                description: $line['description'] ?? null,
            );
        }

        return $this->journals->createDraft($company, $book, $header, $inputs);
    }

    public function postJournal(Journal $journal, ?User $poster = null): Journal
    {
        return $this->journals->post($journal, $poster);
    }

    /** @return LengthAwarePaginator<int, JournalLine> */
    public function listJournalLines(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        $query = JournalLine::query()
            ->whereHas('journal', function ($q) use ($company, $book): void {
                $q->where('company_id', $company->id)->where('book_id', $book->id);
            })
            ->with(['journal', 'account']);

        if ($request->filled('account_code')) {
            $query->whereHas('account', fn ($q) => $q->where('code', $request->input('account_code')));
        }

        return ApiQuery::apply(
            $query,
            $request,
            ['id', 'account_id', 'created_at'],
            ['journal_id' => 'journal_id', 'account_id' => 'account_id'],
            'id',
        );
    }

    /** @return array<string, mixed> */
    public function trialBalance(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        $rows = $this->trialBalance->build($company, $book, $asOf);
        $totals = $this->trialBalance->totals($company, $book, $asOf);

        return [
            'rows' => $rows->values()->all(),
            'totals' => $totals,
        ];
    }

    /** @return LengthAwarePaginator<int, JournalLine> */
    public function generalLedger(Company $company, AccountingBook $book, Request $request): LengthAwarePaginator
    {
        $query = JournalLine::query()
            ->whereHas('journal', function ($q) use ($company, $book, $request): void {
                $q->where('company_id', $company->id)
                    ->where('book_id', $book->id)
                    ->where('status', 'posted');
                if ($request->filled('from')) {
                    $q->whereDate('journal_date', '>=', (string) $request->input('from'));
                }
                if ($request->filled('to')) {
                    $q->whereDate('journal_date', '<=', (string) $request->input('to'));
                }
            })
            ->with(['journal', 'account']);

        if ($request->filled('account_code')) {
            $query->whereHas('account', fn ($q) => $q->where('code', $request->input('account_code')));
        }

        return ApiQuery::apply($query, $request, ['id', 'account_id'], [], 'id');
    }

    /** @return array<int, array{code: string, name_ar: string, debit: string, credit: string, balance: string}> */
    public function accountBalances(Company $company, AccountingBook $book, ?Carbon $asOf = null): array
    {
        return $this->trialBalance->build($company, $book, $asOf)
            ->map(fn ($row) => [
                'code' => $row->code,
                'name_ar' => $row->nameAr,
                'debit' => $row->debit,
                'credit' => $row->credit,
                'balance' => $row->balance,
            ])
            ->values()
            ->all();
    }

    /** @return LengthAwarePaginator<int, FiscalPeriod> */
    public function listPeriods(Company $company, Request $request): LengthAwarePaginator
    {
        return ApiQuery::apply(
            FiscalPeriod::query()->where('company_id', $company->id),
            $request,
            ['id', 'period_no', 'start_date', 'end_date', 'status'],
            ['status' => 'status', 'period_no' => 'period_no'],
            'period_no',
        );
    }

    public function transitionPeriod(FiscalPeriod $period, PeriodStatus $status, ?int $userId = null, ?string $reason = null): FiscalPeriod
    {
        return match ($status) {
            PeriodStatus::SOFT_CLOSED => $this->periods->softClose($period, $userId),
            PeriodStatus::HARD_CLOSED => $this->periods->hardClose($period, $userId),
            PeriodStatus::LOCKED => $this->periods->lock($period, $userId),
            PeriodStatus::OPEN => $this->periods->reopen($period, (string) $reason, $userId),
        };
    }

    /** @return array<string, mixed> */
    public function balanceSheet(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        return $this->statements->balanceSheet(new ReportRequest(
            companyId: $company->id,
            bookId: $book->id,
            asOf: $asOf,
        ));
    }

    /** @return array<string, mixed> */
    public function profitAndLoss(Company $company, AccountingBook $book, ?string $asOf = null): array
    {
        return $this->statements->incomeStatement(new ReportRequest(
            companyId: $company->id,
            bookId: $book->id,
            asOf: $asOf,
        ));
    }
}
