<?php

declare(strict_types=1);

use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns me context for authenticated users with grants', function () {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $user = User::factory()->create();
    $company = Company::firstOrFail();
    $access = app(AccessControl::class);
    foreach (ApiPermission::all() as $permission) {
        $access->grant($user, $company, $permission);
    }

    $this->actingAs($user)
        ->getJson('/api/v1/me/context')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.companies.0.code', 'CO-001')
        ->assertJsonStructure(['data' => ['user', 'companies']]);
});
