<?php

declare(strict_types=1);

use App\Livewire\Dashboard\FinanceDashboard;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Ar\CustomerService;
use App\Services\Ar\SalesInvoiceService;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeLivewire(FinanceDashboard::class);
});

test('finance dashboard livewire renders arabic financial shell', function () {
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    $this->actingAs($user);

    Livewire::test(FinanceDashboard::class)
        ->assertOk()
        ->assertSee(__('erp.dashboard'));
});

test('dashboard cash excludes trade receivables', function () {
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $company = Company::firstOrFail();
    $book = AccountingBook::query()
        ->where('company_id', $company->id)
        ->where('code', 'LOCAL')
        ->firstOrFail();
    $customer = app(CustomerService::class)->create($company, [
        'code' => 'C-DASH-CASH',
        'name_ar' => 'عميل اختبار النقدية',
    ]);
    $invoice = app(SalesInvoiceService::class)->createDraft($company, $book, $customer, [
        ['revenue_account' => '410101', 'net' => 1000],
    ], ['invoice_date' => '2026-03-10']);
    app(SalesInvoiceService::class)->post($invoice, $user->id);

    $this->actingAs($user);

    $component = Livewire::test(FinanceDashboard::class)->assertOk();

    expect($component->viewData('kpis')['cash'])->toBe('0');
    expect((float) $component->viewData('kpis')['ar_total'])->toBe(1000.0);
});

test('locale switch persists arabic rtl and english ltr', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('locale.switch', 'en'))
        ->assertRedirect();

    expect(session('locale'))->toBe('en');

    $this->post(route('locale.switch', 'ar'))
        ->assertRedirect();

    expect(session('locale'))->toBe('ar');
});
