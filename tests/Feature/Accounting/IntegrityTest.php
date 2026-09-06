<?php

declare(strict_types=1);

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Services\Accounting\Data\LineInput;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use App\Services\Accounting\Integrity\IntegrityCheck;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Accounting\JournalService;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);

    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->integrity = app(IntegrityService::class);
    $journals = app(JournalService::class);
    $acc = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail()->id;

    $journals->createAndPost($this->company, $this->book, ['journal_date' => '2026-01-05'], [
        LineInput::debit($acc('110102'), 10000),
        LineInput::credit($acc('310101'), 10000),
    ]);
    $journals->createAndPost($this->company, $this->book, ['journal_date' => '2026-02-05'], [
        LineInput::debit($acc('110201'), 1000),   // AR
        LineInput::credit($acc('410101'), 1000),  // revenue
    ]);
});

it('passes all invariants on a clean ledger', function () {
    $result = $this->integrity->check($this->company, $this->book);

    expect($result['passed'])->toBeTrue();
    foreach ($result['checks'] as $check) {
        expect($check->status)->toBe(IntegrityCheck::PASS);
    }
    // assert() does not throw on a clean ledger
    $this->integrity->assert($this->company, $this->book);
    expect(true)->toBeTrue();
});

it('reconciles a control account against a matching subledger total', function () {
    $check = $this->integrity->controlEqualsSubledger($this->company, $this->book, '110201', '1000', 'INV-7');

    expect($check->passed())->toBeTrue()
        ->and((float) $check->expected)->toBe(1000.0);
});

it('flags a subledger that disagrees with its control account', function () {
    $check = $this->integrity->controlEqualsSubledger($this->company, $this->book, '110201', '900', 'INV-7');

    expect($check->failed())->toBeTrue()
        ->and((float) $check->difference)->toBe(100.0); // GL 1000 vs subledger 900
});

it('detects injected ledger corruption and raises an exception', function () {
    // Corrupt one posted line directly in the DB (bypassing the engine).
    $arAccountId = Account::where('company_id', $this->company->id)->where('code', '110201')->value('id');
    $lineId = DB::table('journal_lines')->where('account_id', $arAccountId)->where('functional_debit', '>', 0)->value('id');
    DB::table('journal_lines')->where('id', $lineId)->update(['functional_debit' => '1050.000000']);

    $result = $this->integrity->check($this->company, $this->book);
    expect($result['passed'])->toBeFalse();

    $inv1 = collect($result['checks'])->firstWhere('code', 'INV-1');
    expect($inv1->failed())->toBeTrue();

    $this->integrity->assert($this->company, $this->book);
})->throws(IntegrityViolationException::class);
