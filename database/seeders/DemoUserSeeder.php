<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Database\Seeder;

/**
 * Demo ERP operator with full API permissions on the seeded company.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $company = Company::query()->where('code', 'CO-001')->first();
        if ($company === null) {
            return;
        }

        $access = app(AccessControl::class);
        foreach (ApiPermission::all() as $permission) {
            $access->grant($user, $company, $permission);
        }
        $access->grant($user, $company, 'journal.approve');
        $access->grant($user, $company, 'journal.post');
        $access->grant($user, $company, 'period.reopen');
    }
}
