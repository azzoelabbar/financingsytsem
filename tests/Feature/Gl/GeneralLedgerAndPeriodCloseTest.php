<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\AuditLog;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use App\Services\Accounting\TrialBalanceService;
use App\Services\Gl\AccrualService;
use App\Services\Gl\Exceptions\PeriodCloseException;
use App\Services\Gl\ManualJournalService;
use App\Services\Gl\PeriodCloseService;
use App\Services\Gl\PrepaymentService;
use App\Services\Gl\RecurringJournalService;
use App\Services\Gl\YearEndCloseService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();
    $this->period = fn (int $no) => FiscalPeriod::where('company_id', $this->company->id)->where('period_no', $no)->firstOrFail();

    $this->manual = app(ManualJournalService::class);
    $this->recurring = app(RecurringJournalService::class);
    $this->accruals = app(AccrualService::class);
    $this->prepayments = app(PrepaymentService::class);
    $this->periods = app(PeriodService::class);
    $this->close = app(PeriodCloseService::class);
    $this->yearEnd = app(YearEndCloseService::class);
});

function gl_line($journal, callable $acc, string $code)
{
    return $journal->lines->firstWhere('account_id', $acc($code)->id);
}

it('runs manual journal draft → submit → approve → post', function () {
    $draft = $this->manual->createDraft($this->company, $this->book, [
        'journal_date' => '2026-03-10',
        'description' => 'Manual adjustment',
    ], [
        LineInput::debit(($this->acc)('620201')->id, 250),
        LineInput::credit(($this->acc)('110102')->id, 250),
    ]);

    expect($draft->status)->toBe(JournalStatus::DRAFT);

    $this->manual->submit($draft);
    expect($draft->fresh()->status)->toBe(JournalStatus::PENDING);

    $this->manual->approve($draft->fresh());
    expect($draft->fresh()->status)->toBe(JournalStatus::APPROVED);

    $posted = $this->manual->post($draft->fresh());
    expect($posted->status)->toBe(JournalStatus::POSTED)
        ->and($posted->number)->not->toBeNull()
        ->and((float) gl_line($posted->load('lines'), $this->acc, '620201')->debit)->toBe(250.0);

    expect(AuditLog::where('auditable_id', $posted->id)->where('event', 'posted')->exists())->toBeTrue();
    expect(app(TrialBalanceService::class)->totals($this->company, $this->book)['balanced'])->toBeTrue();
});

it('voids an unposted manual journal', function () {
    $draft = $this->manual->createDraft($this->company, $this->book, ['journal_date' => '2026-03-10'], [
        LineInput::debit(($this->acc)('620201')->id, 100),
        LineInput::credit(($this->acc)('110102')->id, 100),
    ]);
    $this->manual->void($draft, reason: 'Cancelled');
    expect($draft->fresh()->status)->toBe(JournalStatus::VOID);
});

it('posts a recurring journal template and advances next_run_date', function () {
    $template = $this->recurring->createTemplate($this->company, $this->book, [
        'code' => 'RENT-M',
        'name' => 'Monthly rent',
        'frequency' => 'monthly',
        'day_of_month' => 1,
        'start_date' => '2026-01-01',
        'next_run_date' => '2026-03-01',
    ], [
        ['account_code' => '620201', 'debit' => 500, 'credit' => 0],
        ['account_code' => '210202', 'credit' => 500, 'debit' => 0],
    ]);

    $journals = $this->recurring->runDue($this->company, Carbon::parse('2026-03-01'));
    expect($journals)->toHaveCount(1)
        ->and($journals[0]->source)->toBe('recurring')
        ->and($journals[0]->is_system_generated)->toBeTrue()
        ->and((float) gl_line($journals[0]->load('lines'), $this->acc, '620201')->debit)->toBe(500.0);

    expect($template->fresh()->next_run_date->toDateString())->toBe('2026-04-01');
});

it('posts an accrual and auto-reverses in the next period', function () {
    $accrual = $this->accruals->createDraft($this->company, $this->book, [
        'accrual_date' => '2026-03-31',
        'expense_account_code' => '620201',
        'accrual_account_code' => '210203',
        'amount' => 1200,
        'description' => 'Accrued rent',
    ]);
    expect($accrual->reversal_date->toDateString())->toBe('2026-04-01');

    $posted = $this->accruals->post($accrual);
    expect($posted->status)->toBe('posted')
        ->and($posted->journal_id)->not->toBeNull()
        ->and((float) gl_line($posted->journal()->with('lines')->first(), $this->acc, '620201')->debit)->toBe(1200.0)
        ->and((float) gl_line($posted->journal()->with('lines')->first(), $this->acc, '210203')->credit)->toBe(1200.0);

    $reversed = $this->accruals->reverseDue(Carbon::parse('2026-04-01'));
    expect($reversed)->toHaveCount(1)
        ->and($reversed[0]->status)->toBe('reversed')
        ->and($reversed[0]->reversal_journal_id)->not->toBeNull();

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
});

it('recognizes a prepayment and amortizes on schedule', function () {
    $pp = $this->prepayments->createDraft($this->company, $this->book, [
        'prepayment_date' => '2026-01-01',
        'prepaid_account_code' => '110401',
        'expense_account_code' => '620201',
        'funding_account_code' => '110102',
        'amount' => 900,
        'periods' => 3,
    ]);
    expect($pp->schedules)->toHaveCount(3)
        ->and((float) $pp->schedules->sum(fn ($s) => (float) $s->amount))->toBe(900.0);

    $active = $this->prepayments->post($pp);
    expect($active->status)->toBe('active')
        ->and((float) gl_line($active->journal()->with('lines')->first(), $this->acc, '110401')->debit)->toBe(900.0)
        ->and((float) gl_line($active->journal()->with('lines')->first(), $this->acc, '110102')->credit)->toBe(900.0);

    $first = $this->prepayments->recognizeDue(Carbon::parse('2026-01-01'));
    expect($first)->toHaveCount(1)
        ->and((float) gl_line($first[0]->journal()->with('lines')->first(), $this->acc, '620201')->debit)->toBe(300.0)
        ->and((float) $active->fresh()->amount_recognized)->toBe(300.0)
        ->and($active->fresh()->status)->toBe('active');

    $this->prepayments->recognizeDue(Carbon::parse('2026-03-01'));
    expect($active->fresh()->status)->toBe('completed')
        ->and((float) $active->fresh()->amount_recognized)->toBe(900.0)
        ->and((float) $active->fresh()->remaining())->toBe(0.0);

    expect(app(IntegrityService::class)->check($this->company, $this->book)['passed'])->toBeTrue();
});

it('soft-closes via month-end checklist when invariants hold', function () {
    app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-05'], [
        LineInput::debit(($this->acc)('110102')->id, 2000),
        LineInput::credit(($this->acc)('310101')->id, 2000),
    ]);

    $period = ($this->period)(3);
    $run = $this->close->run($this->company, $this->book, $period, 'soft_closed');

    expect($run->all_passed)->toBeTrue()
        ->and($run->status)->toBe('passed')
        ->and($run->items->where('status', 'fail')->count())->toBe(0)
        ->and($run->items->firstWhere('code', 'SUSPENSE')->status)->toBe('pass')
        ->and($run->items->firstWhere('code', 'INV_RECON')->status)->toBe('pass');

    $closed = $this->close->closeIfPassed($run);
    expect($closed->status)->toBe('closed')
        ->and($period->fresh()->status)->toBe(PeriodStatus::SOFT_CLOSED);

    // Privileged posting still allowed into soft-closed; normal posting is not.
    expect(fn () => app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-20'], [
        LineInput::debit(($this->acc)('620201')->id, 50),
        LineInput::credit(($this->acc)('110102')->id, 50),
    ]))->toThrow(PostingException::class);

    $draft = app(JournalService::class)->createDraft($this->company, $this->book, ['journal_date' => '2026-03-20'], [
        LineInput::debit(($this->acc)('620201')->id, 50),
        LineInput::credit(($this->acc)('110102')->id, 50),
    ]);
    expect(app(JournalService::class)->post($draft, allowSoftClosed: true)->status)->toBe(JournalStatus::POSTED);
});

it('refuses to close when checklist fails (draft accrual)', function () {
    $this->accruals->createDraft($this->company, $this->book, [
        'accrual_date' => '2026-03-15',
        'expense_account_code' => '620201',
        'accrual_account_code' => '210203',
        'amount' => 100,
    ]);

    $run = $this->close->run($this->company, $this->book, ($this->period)(3), 'soft_closed');
    expect($run->all_passed)->toBeFalse()
        ->and($run->items->firstWhere('code', 'ACCRUALS')->status)->toBe('fail');

    expect(fn () => $this->close->closeIfPassed($run))->toThrow(PeriodCloseException::class);
});

it('hard-closes and locks; reopen requires reason and refuses locked', function () {
    $period = ($this->period)(2);
    $this->periods->softClose($period);
    $this->periods->hardClose($period->fresh());
    expect($period->fresh()->status)->toBe(PeriodStatus::HARD_CLOSED);

    expect(fn () => app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-10'], [
        LineInput::debit(($this->acc)('620201')->id, 10),
        LineInput::credit(($this->acc)('110102')->id, 10),
    ]))->toThrow(PostingException::class);

    $this->periods->reopen($period->fresh(), 'Audit adjustment');
    expect($period->fresh()->status)->toBe(PeriodStatus::OPEN);

    $this->periods->hardClose($period->fresh());
    $this->periods->lock($period->fresh());
    expect($period->fresh()->status)->toBe(PeriodStatus::LOCKED);
    expect(fn () => $this->periods->reopen($period->fresh(), 'try'))->toThrow(PeriodCloseException::class);
});

it('closes year-end P&L into retained earnings keeping INV-2', function () {
    app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-01'], [
        LineInput::debit(($this->acc)('110102')->id, 5000),
        LineInput::credit(($this->acc)('410101')->id, 5000),
    ]);
    app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('620201')->id, 1500),
        LineInput::credit(($this->acc)('110102')->id, 1500),
    ]);

    $period = ($this->period)(3);
    $je = $this->yearEnd->closeToRetainedEarnings($this->company, $this->book, $period);

    expect($je->source)->toBe('year_end_close')
        ->and((float) gl_line($je->load('lines'), $this->acc, '410101')->debit)->toBe(5000.0)
        ->and((float) gl_line($je->load('lines'), $this->acc, '620201')->credit)->toBe(1500.0)
        ->and((float) gl_line($je->load('lines'), $this->acc, '320201')->credit)->toBe(3500.0);

    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id, asOf: '2026-03-31'));
    expect($bs['balanced'])->toBeTrue();
    expect(app(IntegrityService::class)->check($this->company, $this->book, Carbon::parse('2026-03-31'))['passed'])->toBeTrue();
});

it('keeps opening rollforward balanced after hard-close into next period', function () {
    app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-01-15'], [
        LineInput::debit(($this->acc)('110102')->id, 10000),
        LineInput::credit(($this->acc)('310101')->id, 10000),
    ]);

    $this->periods->hardClose(($this->period)(1));

    app(JournalService::class)->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-10'], [
        LineInput::debit(($this->acc)('620201')->id, 400),
        LineInput::credit(($this->acc)('110102')->id, 400),
    ]);

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    $bs = app(FinancialStatementService::class)->balanceSheet(new ReportRequest($this->company->id, $this->book->id));
    expect($tb['balanced'])->toBeTrue()->and($bs['balanced'])->toBeTrue();
});

it('fails INV-11 when suspense has an unexplained balance', function () {
    app(JournalService::class)->createAndPost($this->company, $this->book, [
        'journal_date' => '2026-03-10',
        'source' => 'system',
        'is_system_generated' => true,
    ], [
        LineInput::debit(($this->acc)('110702')->id, 75),
        LineInput::credit(($this->acc)('110102')->id, 75),
    ]);

    $check = app(IntegrityService::class)->suspenseCleared($this->company, $this->book);
    expect($check->failed())->toBeTrue();

    $run = $this->close->run($this->company, $this->book, ($this->period)(3));
    expect($run->items->firstWhere('code', 'SUSPENSE')->status)->toBe('fail')
        ->and($run->all_passed)->toBeFalse();
});
