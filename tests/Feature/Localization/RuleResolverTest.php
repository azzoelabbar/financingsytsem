<?php

declare(strict_types=1);

use App\Models\Localization\LocalizationRule;
use App\Services\Localization\Exceptions\RuleNotFoundException;
use App\Services\Localization\RuleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * NOTE: the rates below are SYNTHETIC test data used only to exercise the
 * resolution engine — they are not Libyan legal values and assert no law.
 */
beforeEach(function () {
    $this->resolver = app(RuleResolver::class);

    $base = fn (array $o) => array_merge([
        'country' => 'LY',
        'rule_code' => 'LY_VAT_STANDARD',
        'rule_type' => 'tax_rate',
        'name_ar' => 'ضريبة القيمة المضافة',
        'authority' => 'TEST',
        'legal_reference' => 'TEST',
    ], $o);

    // v1 active 2020..2023
    LocalizationRule::create($base(['version' => 1, 'status' => 'active', 'rate' => '0.10', 'effective_from' => '2020-01-01', 'effective_to' => '2023-12-31']));
    // v2 active 2024.. open-ended (supersedes v1 going forward)
    LocalizationRule::create($base(['version' => 2, 'status' => 'active', 'rate' => '0.15', 'effective_from' => '2024-01-01', 'effective_to' => null]));
    // v3 still a draft — must be ignored
    LocalizationRule::create($base(['version' => 3, 'status' => 'draft', 'rate' => '0.20', 'effective_from' => '2020-01-01', 'effective_to' => null]));
    // A different country, same code — isolation check
    LocalizationRule::create($base(['country' => 'AE', 'version' => 1, 'status' => 'active', 'rate' => '0.05', 'effective_from' => '2018-01-01', 'effective_to' => null]));
});

it('resolves the version effective on the requested date', function () {
    $rule = $this->resolver->resolve('LY', 'LY_VAT_STANDARD', Carbon::parse('2022-06-01'));

    expect($rule->version)->toBe(1)
        ->and((float) $this->resolver->rate('LY', 'LY_VAT_STANDARD', Carbon::parse('2022-06-01')))->toBe(0.10);
});

it('uses the newer version once it takes effect', function () {
    expect((float) $this->resolver->rate('LY', 'LY_VAT_STANDARD', Carbon::parse('2025-03-01')))->toBe(0.15);
});

it('ignores draft rules', function () {
    // Even though a draft rate 0.20 exists effective 2020, resolution never returns it.
    expect((float) $this->resolver->rate('LY', 'LY_VAT_STANDARD', Carbon::parse('2025-03-01')))->toBe(0.15);
});

it('isolates rules by country', function () {
    expect((float) $this->resolver->rate('AE', 'LY_VAT_STANDARD', Carbon::parse('2025-03-01')))->toBe(0.05);
});

it('throws (no hidden default) when no rule is effective on the date', function () {
    $this->resolver->resolve('LY', 'LY_VAT_STANDARD', Carbon::parse('2019-01-01'));
})->throws(RuleNotFoundException::class);

it('throws for an unknown rule code', function () {
    $this->resolver->resolve('LY', 'LY_DOES_NOT_EXIST', Carbon::parse('2025-01-01'));
})->throws(RuleNotFoundException::class);

it('throws rather than guessing when a rule has no sourced value', function () {
    LocalizationRule::create([
        'country' => 'LY', 'rule_code' => 'LY_CIT', 'rule_type' => 'tax_rate', 'name_ar' => 'ضريبة دخل الشركات',
        'authority' => 'TEST', 'legal_reference' => 'TEST', 'version' => 1, 'status' => 'active',
        'rate' => null, 'effective_from' => '2020-01-01', 'effective_to' => null,
    ]);

    $this->resolver->rate('LY', 'LY_CIT', Carbon::parse('2025-01-01'));
})->throws(RuleNotFoundException::class);
