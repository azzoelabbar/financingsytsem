<?php

declare(strict_types=1);

use App\Models\Accounting\Currency;
use App\Models\User;
use App\Support\Registration;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// `migrate:fresh --seed` runs DatabaseSeeder. If that ever creates a user
// again, the single account slot is taken before the owner can register and
// the sign-up form is closed on a brand new install.
test('the default seeder leaves the install with no accounts', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(0)
        ->and(Registration::isOpen())->toBeTrue();
});

test('the default seeder still installs the currencies', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Currency::query()->where('is_active', true)->count())->toBeGreaterThanOrEqual(20);
});

test('a freshly seeded install still offers the registration form', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('home'))->assertRedirect(route('register'));
    $this->get(route('register'))->assertOk();
});

test('the demo workspace is opt-in and does create its operator', function () {
    $this->seed(DemoSeeder::class);

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeTrue()
        ->and(Registration::isOpen())->toBeFalse();
});
