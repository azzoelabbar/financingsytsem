<?php

declare(strict_types=1);

use App\Models\Accounting\Account;
use App\Models\Accounting\Company;
use App\Models\Accounting\Organization;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * These tests cover the LEGACY 168-account chart (backward-compatibility). The
 * canonical chart is the 330-account enterprise set (DemoCompanySeeder); the
 * legacy seeder is exercised explicitly here so it stays regression-tested.
 */
beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $org = Organization::create(['code' => 'ORG-LEGACY', 'name_ar' => 'مؤسسة قديمة', 'default_currency' => 'LYD', 'country' => 'LY']);
    $this->company = Company::create([
        'organization_id' => $org->id,
        'code' => 'CO-LEGACY',
        'name_ar' => 'شركة الدليل القديم',
        'functional_currency' => 'LYD',
        'presentation_currency' => 'LYD',
        'country' => 'LY',
        'accounting_framework' => 'local_gaap',
    ]);
    (new ChartOfAccountsSeeder)->seedForCompany($this->company);
});

it('imports the full supplied chart of accounts', function () {
    expect(Account::where('company_id', $this->company->id)->count())->toBe(168);
});

it('builds a clean account hierarchy where every non-root account has a parent', function () {
    $orphans = Account::query()
        ->where('company_id', $this->company->id)
        ->whereNull('parent_id')
        ->where('level', '>', 1)
        ->count();

    expect($orphans)->toBe(0);

    // The seven top-level classes are the only roots.
    expect(Account::where('company_id', $this->company->id)->whereNull('parent_id')->count())->toBe(7);
});

it('marks only leaf accounts as postable', function () {
    $parentButPosting = Account::query()
        ->where('company_id', $this->company->id)
        ->where('is_posting', true)
        ->whereIn('id', function ($q) {
            $q->select('parent_id')->from('accounts')->whereNotNull('parent_id');
        })
        ->count();

    expect($parentButPosting)->toBe(0);
});

it('detects contra accounts by their flipped natural balance', function () {
    $byCode = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();

    // Accumulated depreciation, doubtful-debt allowance, sales & purchase returns.
    expect($byCode('110204')->is_contra)->toBeTrue()   // مخصص الديون المشكوك فيها
        ->and($byCode('120103')->is_contra)->toBeTrue() // م.م. مباني
        ->and($byCode('410103')->is_contra)->toBeTrue() // مردودات المبيعات
        ->and($byCode('510103')->is_contra)->toBeTrue() // مردودات المشتريات
        // A normal cash account must NOT be contra.
        ->and($byCode('110101')->is_contra)->toBeFalse();
});

it('locks closing accounts against manual journals', function () {
    $closing = Account::where('company_id', $this->company->id)->where('code', '710101')->firstOrFail();

    expect($closing->system_generated_only)->toBeTrue()
        ->and($closing->manual_journal_allowed)->toBeFalse()
        ->and($closing->account_type->value)->toBe('closing');
});

it('flags subledger and bank accounts', function () {
    $byCode = fn (string $code) => Account::where('company_id', $this->company->id)->where('code', $code)->firstOrFail();

    expect($byCode('110201')->is_customer_subledger)->toBeTrue()  // العملاء
        ->and($byCode('210101')->is_supplier_subledger)->toBeTrue() // الموردون
        ->and($byCode('110102')->is_bank_account)->toBeTrue();      // البنك الرئيسي
});
