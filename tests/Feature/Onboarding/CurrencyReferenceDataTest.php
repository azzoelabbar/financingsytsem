<?php

declare(strict_types=1);

use App\Livewire\Onboarding\AccountingSetup;
use App\Models\Accounting\Currency;
use App\Models\User;
use Database\Seeders\AccountingReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('the reference seeder installs the currency list', function () {
    $this->seed(AccountingReferenceSeeder::class);

    expect(Currency::query()->where('is_active', true)->count())->toBeGreaterThanOrEqual(20)
        ->and(Currency::query()->whereIn('code', ['LYD', 'USD', 'EUR', 'SAR', 'EGP', 'TND'])->count())->toBe(6);
});

test('three-decimal currencies keep their real minor units', function () {
    $this->seed(AccountingReferenceSeeder::class);

    foreach (['LYD', 'TND', 'KWD', 'BHD', 'OMR', 'JOD', 'IQD'] as $code) {
        expect(Currency::query()->findOrFail($code)->decimal_places)->toBe(3);
    }

    expect(Currency::query()->findOrFail('JPY')->decimal_places)->toBe(0)
        ->and(Currency::query()->findOrFail('USD')->decimal_places)->toBe(2);
});

test('re-running the seeder is idempotent and respects a deactivated currency', function () {
    $this->seed(AccountingReferenceSeeder::class);
    $before = Currency::query()->count();

    Currency::query()->findOrFail('JPY')->update(['is_active' => false]);

    $this->seed(AccountingReferenceSeeder::class);

    expect(Currency::query()->count())->toBe($before)
        ->and(Currency::query()->findOrFail('JPY')->is_active)->toBeFalse();
});

test('the onboarding currency dropdowns are populated', function () {
    $this->seed(AccountingReferenceSeeder::class);

    $user = User::create([
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);
    session(['onboarding.company' => [
        'name_ar' => 'شركة',
        'code' => 'CO-001',
        'country' => 'LY',
    ]]);

    Livewire::test(AccountingSetup::class)
        ->assertOk()
        ->assertSee('LYD')
        ->assertSee('USD')
        ->assertSee('Libyan Dinar');

    // The same list in Arabic, which is what the operator actually sees.
    app()->setLocale('ar');

    Livewire::test(AccountingSetup::class)
        ->assertOk()
        ->assertSee('دينار ليبي');
});
