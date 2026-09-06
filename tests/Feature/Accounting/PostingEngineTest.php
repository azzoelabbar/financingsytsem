<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Enums\Accounting\PeriodStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\AuditLog;
use App\Models\Accounting\Company;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Journal;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Exceptions\UnbalancedJournalException;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\TrialBalanceService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->service = app(JournalService::class);

    $this->acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();
});

it('posts a balanced journal and assigns a number', function () {
    $journal = $this->service->createAndPost($this->company, $this->book, [
        'journal_date' => '2026-03-15',
        'description' => 'بيع نقدي',
    ], [
        LineInput::debit(($this->acc)('110101')->id, 1000),   // الصندوق
        LineInput::credit(($this->acc)('410101')->id, 1000),  // مبيعات محلية
    ]);

    expect($journal->status)->toBe(JournalStatus::POSTED)
        ->and($journal->number)->not->toBeNull()
        ->and((float) $journal->total_debit)->toBe(1000.0)
        ->and((float) $journal->total_credit)->toBe(1000.0)
        ->and($journal->isBalanced())->toBeTrue()
        ->and($journal->fiscal_period_id)->not->toBeNull();
});

it('rejects an unbalanced journal', function () {
    $this->service->createDraft($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('110101')->id, 1000),
        LineInput::credit(($this->acc)('410101')->id, 900),
    ]);
})->throws(UnbalancedJournalException::class);

it('refuses posting to a summary (non-posting) account', function () {
    $this->service->createDraft($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('11')->id, 1000),        // الأصول المتداولة (group)
        LineInput::credit(($this->acc)('410101')->id, 1000),
    ]);
})->throws(PostingException::class);

it('refuses a manual journal to a system-only closing account', function () {
    $this->service->createDraft($this->company, $this->book, ['journal_date' => '2026-03-15', 'source' => 'manual'], [
        LineInput::debit(($this->acc)('710101')->id, 1000),    // الأرباح والخسائر
        LineInput::credit(($this->acc)('410101')->id, 1000),
    ]);
})->throws(PostingException::class);

it('refuses posting into a hard-closed period', function () {
    FiscalPeriod::where('company_id', $this->company->id)
        ->where('period_no', 3)
        ->update(['status' => PeriodStatus::HARD_CLOSED]);

    $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('110101')->id, 1000),
        LineInput::credit(($this->acc)('410101')->id, 1000),
    ]);
})->throws(PostingException::class);

it('reverses a posted journal with a mirror entry and nets to zero', function () {
    $original = $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('110101')->id, 1000),
        LineInput::credit(($this->acc)('410101')->id, 1000),
    ]);

    $reversal = $this->service->reverse($original->fresh(), reason: 'تصحيح');

    expect($original->fresh()->status)->toBe(JournalStatus::REVERSED)
        ->and($original->fresh()->reversed_by_journal_id)->toBe($reversal->id)
        ->and($reversal->status)->toBe(JournalStatus::POSTED);

    // Debit/credit are swapped on the mirror.
    $origCashLine = $original->lines()->where('account_id', ($this->acc)('110101')->id)->first();
    $revCashLine = $reversal->lines()->where('account_id', ($this->acc)('110101')->id)->first();
    expect((float) $origCashLine->debit)->toBe(1000.0)
        ->and((float) $revCashLine->credit)->toBe(1000.0);

    // Net effect on the ledger is zero.
    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue()
        ->and((float) $tb['debit'])->toBe(2000.0); // 1000 original + 1000 reversal, both sides
});

it('keeps the trial balance balanced across multiple postings', function () {
    $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-01-10'], [
        LineInput::debit(($this->acc)('110102')->id, 5000),    // البنك الرئيسي
        LineInput::credit(($this->acc)('310101')->id, 5000),   // رأس المال
    ]);

    $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-10'], [
        LineInput::debit(($this->acc)('620201')->id, 1200),    // إيجار
        LineInput::credit(($this->acc)('110102')->id, 1200),
    ]);

    $tb = app(TrialBalanceService::class)->totals($this->company, $this->book);
    expect($tb['balanced'])->toBeTrue();
});

it('enforces mandatory analytical dimensions', function () {
    $rentAccount = ($this->acc)('620201');
    $rentAccount->update(['requires_cost_center' => true]);

    // Missing the cost-center dimension → rejected.
    expect(fn () => $this->service->createDraft($this->company, $this->book, ['journal_date' => '2026-02-10'], [
        LineInput::debit($rentAccount->id, 1000),
        LineInput::credit(($this->acc)('110102')->id, 1000),
    ]))->toThrow(PostingException::class);

    // With the cost-center dimension provided → accepted.
    $dim = Dimension::where('company_id', $this->company->id)->where('code', 'COST_CENTER')->firstOrFail();
    $value = $dim->values()->firstOrFail();

    $journal = $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-10'], [
        new LineInput($rentAccount->id, debit: 1000, dimensions: [$dim->id => $value->id]),
        LineInput::credit(($this->acc)('110102')->id, 1000),
    ]);

    expect($journal->status)->toBe(JournalStatus::POSTED);
});

it('writes an audit trail entry when a journal is posted', function () {
    $journal = $this->service->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-15'], [
        LineInput::debit(($this->acc)('110101')->id, 1000),
        LineInput::credit(($this->acc)('410101')->id, 1000),
    ]);

    $events = AuditLog::where('auditable_type', Journal::class)
        ->where('auditable_id', $journal->id)
        ->pluck('event');

    expect($events)->toContain('created')->toContain('posted');
});
