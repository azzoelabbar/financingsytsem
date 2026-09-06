<?php

declare(strict_types=1);

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\Reporting\Data\ReportRequest;
use App\Services\Accounting\Reporting\FinancialStatementService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $journals = app(JournalService::class);
    $acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail()->id;

    // Capital injection: Dr Bank / Cr Capital 10,000
    $journals->createAndPost($this->company, $this->book, ['journal_date' => '2026-01-05'], [
        LineInput::debit($acc('110102'), 10000),
        LineInput::credit($acc('310101'), 10000),
    ]);
    // Cash service sale: Dr Bank / Cr Service revenue 1,000
    $journals->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-05'], [
        LineInput::debit($acc('110102'), 1000),
        LineInput::credit($acc('410201'), 1000),
    ]);
    // Rent expense: Dr Rent / Cr Bank 300
    $journals->createAndPost($this->company, $this->book, ['journal_date' => '2026-03-05'], [
        LineInput::debit($acc('620201'), 300),
        LineInput::credit($acc('110102'), 300),
    ]);

    $this->request = new ReportRequest(
        companyId: $this->company->id,
        bookId: $this->book->id,
        asOf: '2026-12-31',
    );
    $this->service = app(FinancialStatementService::class);
});

it('produces a balanced statement of financial position', function () {
    $bs = $this->service->balanceSheet($this->request);

    expect($bs['balanced'])->toBeTrue()
        ->and((float) $bs['totals']['assets'])->toBe(10700.0)      // bank 10000 + 1000 − 300
        ->and((float) $bs['totals']['liabilities'])->toBe(0.0)
        ->and((float) $bs['totals']['equity'])->toBe(10700.0)      // capital 10000 + result 700
        ->and((float) $bs['totals']['net_result'])->toBe(700.0);
});

it('produces a profit or loss with net profit derived from the ledger', function () {
    $pl = $this->service->incomeStatement($this->request);

    expect((float) $pl['revenue'])->toBe(1000.0)
        ->and((float) $pl['expenses'])->toBe(300.0)
        ->and((float) $pl['net_profit'])->toBe(700.0);
});

it('gives every statement line a drill-down anchor (accountId)', function () {
    $bs = $this->service->balanceSheet($this->request);

    expect($bs['lines'])->not->toBeEmpty();
    foreach ($bs['lines'] as $line) {
        expect($line->accountId)->toBeGreaterThan(0)
            ->and(Account::whereKey($line->accountId)->exists())->toBeTrue();
    }
});

it('honours the as-of date (excludes later postings)', function () {
    $early = new ReportRequest(
        companyId: $this->company->id,
        bookId: $this->book->id,
        asOf: '2026-01-31', // only the capital injection has posted by then
    );

    $bs = $this->service->balanceSheet($early);

    expect((float) $bs['totals']['assets'])->toBe(10000.0)
        ->and((float) $bs['totals']['net_result'])->toBe(0.0)
        ->and($bs['balanced'])->toBeTrue();
});
