<?php

declare(strict_types=1);

use App\Application\Api\Gl\GlApplicationService;
use App\Livewire\Onboarding\AccountingSetup;
use App\Livewire\Onboarding\CompanyDetails;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Security\AccessGrant;
use App\Models\User;
use App\Services\Accounting\Integrity\IntegrityService;
use App\Services\Onboarding\CompanyProvisioningService;
use App\Support\Accounting\AccountingContext;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AccountingReferenceSeeder::class); // currencies + reference data
});

function freshUser(string $email = 'new@example.com'): User
{
    return User::create([
        'name' => 'New User',
        'email' => $email,
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
}

test('a newly registered user with no company is redirected into onboarding', function () {
    $this->actingAs(freshUser());

    $this->get(route('dashboard'))->assertRedirect(route('onboarding.company'));
});

test('an already-onboarded (seeded) user reaches the dashboard without redirect', function () {
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());

    $this->get(route('dashboard'))->assertOk();
});

test('company details validates required fields', function () {
    $this->actingAs(freshUser());

    Livewire::test(CompanyDetails::class)
        ->set('name_ar', '')
        ->set('code', '')
        ->call('continue')
        ->assertHasErrors(['name_ar', 'code']);
});

test('company details stores to session and advances to accounting', function () {
    $this->actingAs(freshUser());

    Livewire::test(CompanyDetails::class)
        ->set('name_ar', 'شركتي التجارية')
        ->set('name_en', 'My Trading Co')
        ->set('code', 'MYCO')
        ->set('country', 'LY')
        ->call('continue')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.accounting'));

    expect(session('onboarding.company'))->toMatchArray(['code' => 'MYCO', 'name_ar' => 'شركتي التجارية']);
});

test('accounting setup validates the currency against the backend', function () {
    $this->actingAs(freshUser());
    session(['onboarding.company' => ['name_ar' => 'ش', 'code' => 'X', 'country' => 'LY']]);

    Livewire::test(AccountingSetup::class)
        ->set('functional_currency', 'ZZZ') // not a seeded currency
        ->call('finish')
        ->assertHasErrors('functional_currency');
});

test('finishing onboarding provisions a full company and grants the user access', function () {
    $user = freshUser();
    $this->actingAs($user);
    session(['onboarding.company' => [
        'name_ar' => 'شركتي', 'name_en' => 'My Co', 'code' => 'MYCO', 'country' => 'LY', 'tax_registration_number' => '12345',
    ]]);

    Livewire::test(AccountingSetup::class)
        ->set('functional_currency', 'LYD')
        ->set('presentation_currency', 'LYD')
        ->set('accounting_framework', 'local_gaap')
        ->set('fiscal_year', 2026)
        ->call('finish')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $company = Company::query()->where('code', 'MYCO')->firstOrFail();

    expect(AccountingBook::query()->where('company_id', $company->id)->count())->toBe(3)
        ->and(AccessGrant::query()->where('user_id', $user->id)->where('company_id', $company->id)->exists())->toBeTrue()
        ->and(Account::query()->where('company_id', $company->id)->count())->toBeGreaterThan(300)
        ->and(app(AccountingContext::class)->companiesFor($user->fresh())->isNotEmpty())->toBeTrue();

    // Wizard state is cleared and the ERP is now reachable.
    expect(session('onboarding.company'))->toBeNull();
    $this->get(route('dashboard'))->assertOk();
});

test('a provisioned company keeps accounting invariants intact', function () {
    $user = freshUser();

    $company = app(CompanyProvisioningService::class)->provision(
        $user,
        ['name_ar' => 'شركة الاختبار', 'code' => 'INV1', 'country' => 'LY'],
        ['functional_currency' => 'LYD'],
    );
    $local = AccountingBook::query()->where('company_id', $company->id)->where('code', 'LOCAL')->firstOrFail();

    $tb = app(GlApplicationService::class)->trialBalance($company, $local);
    expect($tb['totals']['balanced'])->toBeTrue()
        ->and(app(IntegrityService::class)->check($company, $local)['passed'])->toBeTrue();
});

test('onboarding renders in both Arabic and English', function () {
    $this->actingAs(freshUser());

    app()->setLocale('ar');
    Livewire::test(CompanyDetails::class)->assertOk()->assertSee('أخبرنا عن شركتك');

    app()->setLocale('en');
    Livewire::test(CompanyDetails::class)->assertOk()->assertSee('Tell us about your company');
});
