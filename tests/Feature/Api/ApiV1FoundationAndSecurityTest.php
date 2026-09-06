<?php

declare(strict_types=1);

use App\Enums\Accounting\AccountingFramework;
use App\Enums\Accounting\BookBasis;
use App\Enums\Ar\DocumentStatus;
use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Organization;
use App\Models\User;
use App\Services\Accounting\PeriodService;
use App\Services\Ar\CustomerService;
use App\Services\Ar\SalesInvoiceService;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->company = Company::firstOrFail();
    $this->book = AccountingBook::where('company_id', $this->company->id)->where('code', 'LOCAL')->firstOrFail();
    $this->user = User::factory()->create();
    $access = app(AccessControl::class);
    foreach (ApiPermission::all() as $permission) {
        $access->grant($this->user, $this->company, $permission);
    }
    $this->token = $this->user->createToken('test')->plainTextToken;
});

function apiHeaders(): array
{
    return [
        'Authorization' => 'Bearer '.test()->token,
        'X-Company-Id' => (string) test()->company->id,
        'X-Book-Id' => (string) test()->book->id,
        'Accept' => 'application/json',
    ];
}

it('issues a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/token', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'pest',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
});

it('lists customers with pagination filtering and sorting', function () {
    app(CustomerService::class)->create($this->company, [
        'code' => 'API-C1', 'name_ar' => 'عميل', 'currency' => 'LYD',
    ]);

    $response = $this->getJson('/api/v1/ar/customers?per_page=10&sort=code&direction=asc&code=API-C1', apiHeaders());

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data', 'meta' => ['pagination']]);
    expect($response->json('data'))->toHaveCount(1);
});

it('creates and posts a sales invoice through the application layer', function () {
    $customer = app(CustomerService::class)->create($this->company, [
        'code' => 'API-C2', 'name_ar' => 'عميل فاتورة', 'currency' => 'LYD',
    ]);

    $response = $this->postJson('/api/v1/ar/sales-invoices', [
        'customer_id' => $customer->id,
        'invoice_date' => '2026-03-10',
        'number' => 'API-INV-1',
        'lines' => [['revenue_account' => '410101', 'net' => 250, 'tax' => 0]],
        'post' => true,
    ], apiHeaders());

    $response->assertCreated()->assertJsonPath('success', true);
    expect($response->json('data.journal_id'))->not->toBeNull()
        ->and($response->json('data.status'))->toBe(DocumentStatus::POSTED->value);
});

it('exposes AR aging and reports from domain services', function () {
    $this->getJson('/api/v1/ar/aging', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['buckets', 'total']]);

    $this->getJson('/api/v1/reports/balance-sheet', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->getJson('/api/v1/reports/profit-loss', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->getJson('/api/v1/gl/trial-balance', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['rows', 'totals']]);
});

it('exposes AP suppliers books investments and management pack', function () {
    $this->postJson('/api/v1/ap/suppliers', [
        'code' => 'API-S1',
        'legal_name' => 'مورد',
        'currency' => 'LYD',
    ], apiHeaders())->assertCreated();

    $this->getJson('/api/v1/books', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->postJson('/api/v1/investments', [
        'code' => 'API-INV',
        'classification' => 'FVTPL',
        'cost' => 100,
        'date' => '2026-03-05',
    ], apiHeaders())->assertCreated();

    $this->getJson('/api/v1/reports/management-pack', apiHeaders())
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('rejects unauthenticated requests with 401', function () {
    $this->getJson('/api/v1/ar/customers', [
        'X-Company-Id' => (string) $this->company->id,
        'Accept' => 'application/json',
    ])->assertUnauthorized();
});

it('rejects missing permission with 403', function () {
    $limited = User::factory()->create();
    app(AccessControl::class)->grant($limited, $this->company, ApiPermission::AP_READ);
    $token = $limited->createToken('limited')->plainTextToken;

    $this->getJson('/api/v1/ar/customers', [
        'Authorization' => 'Bearer '.$token,
        'X-Company-Id' => (string) $this->company->id,
        'X-Book-Id' => (string) $this->book->id,
        'Accept' => 'application/json',
    ])->assertForbidden();
});

it('rejects wrong company context with 403', function () {
    $org = Organization::query()->firstOrFail();
    $other = Company::create([
        'organization_id' => $org->id,
        'code' => 'OTHER',
        'name_ar' => 'أخرى',
        'functional_currency' => 'LYD',
        'accounting_framework' => AccountingFramework::LOCAL_GAAP,
    ]);

    $this->getJson('/api/v1/ar/customers', [
        'Authorization' => 'Bearer '.$this->token,
        'X-Company-Id' => (string) $other->id,
        'X-Book-Id' => (string) $this->book->id,
        'Accept' => 'application/json',
    ])->assertForbidden();
});

it('rejects unknown book for company with 404', function () {
    $this->getJson('/api/v1/ar/aging', [
        'Authorization' => 'Bearer '.$this->token,
        'X-Company-Id' => (string) $this->company->id,
        'X-Book-Id' => '999999',
        'Accept' => 'application/json',
    ])->assertNotFound();
});

it('rejects cross-company document access', function () {
    $customer = app(CustomerService::class)->create($this->company, [
        'code' => 'ISO-C', 'name_ar' => 'عزل', 'currency' => 'LYD',
    ]);
    $invoice = app(SalesInvoiceService::class)->createDraft(
        $this->company,
        $this->book,
        $customer,
        [['revenue_account' => '410101', 'net' => 10, 'tax' => 0]],
        ['invoice_date' => '2026-03-10', 'number' => 'ISO-INV'],
    );

    $org = Organization::query()->firstOrFail();
    $other = Company::create([
        'organization_id' => $org->id,
        'code' => 'ISO2',
        'name_ar' => 'عزل 2',
        'functional_currency' => 'LYD',
        'accounting_framework' => AccountingFramework::LOCAL_GAAP,
    ]);
    $otherBook = AccountingBook::create([
        'company_id' => $other->id,
        'code' => 'LOCAL',
        'name_ar' => 'محلي',
        'is_primary' => true,
        'basis' => BookBasis::LOCAL,
    ]);
    $intruder = User::factory()->create();
    foreach (ApiPermission::all() as $permission) {
        app(AccessControl::class)->grant($intruder, $other, $permission);
    }
    $token = $intruder->createToken('intruder')->plainTextToken;

    $this->getJson('/api/v1/ar/sales-invoices/'.$invoice->id, [
        'Authorization' => 'Bearer '.$token,
        'X-Company-Id' => (string) $other->id,
        'X-Book-Id' => (string) $otherBook->id,
        'Accept' => 'application/json',
    ])->assertNotFound();
});

it('rejects posted document re-post mutation', function () {
    $customer = app(CustomerService::class)->create($this->company, [
        'code' => 'POST-C', 'name_ar' => 'مرحل', 'currency' => 'LYD',
    ]);
    $invoice = app(SalesInvoiceService::class)->post(app(SalesInvoiceService::class)->createDraft(
        $this->company,
        $this->book,
        $customer,
        [['revenue_account' => '410101', 'net' => 50, 'tax' => 0]],
        ['invoice_date' => '2026-03-11', 'number' => 'POST-INV'],
    ));

    $this->postJson('/api/v1/ar/sales-invoices/'.$invoice->id.'/post', [], apiHeaders())
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'business_rule');
});

it('rejects posting into a hard-closed period', function () {
    $period = FiscalPeriod::where('company_id', $this->company->id)
        ->where('period_no', 3)
        ->firstOrFail();
    app(PeriodService::class)->hardClose($period);

    $customer = app(CustomerService::class)->create($this->company, [
        'code' => 'CLOSED-C', 'name_ar' => 'مغلق', 'currency' => 'LYD',
    ]);

    $this->postJson('/api/v1/ar/sales-invoices', [
        'customer_id' => $customer->id,
        'invoice_date' => '2026-03-15',
        'number' => 'CLOSED-INV',
        'lines' => [['revenue_account' => '410101', 'net' => 20, 'tax' => 0]],
        'post' => true,
    ], apiHeaders())
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'business_rule');
});
