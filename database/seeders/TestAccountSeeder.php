<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accounting\Company;
use App\Models\User;
use App\Services\Security\AccessControl;
use App\Support\Api\ApiPermission;
use Illuminate\Database\Seeder;

/**
 * A named test account for local work.
 *
 *     php artisan db:seed --class=TestAccountSeeder
 *
 * The password is deliberately trivial, so this refuses to run in production —
 * a real install gets its single account through the one-time registration
 * form, never from here.
 */
class TestAccountSeeder extends Seeder
{
    private const EMAIL = 'azzoelabbar@gmail.com';

    private const PASSWORD = '123123123';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('TestAccountSeeder is for local use only — skipped.');

            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Azzo Elabbar',
                'password' => self::PASSWORD,
                'email_verified_at' => now(),
            ],
        );

        // Re-running resets the password and keeps the address verified, so the
        // account always logs in with the documented credentials.
        $user->forceFill([
            'password' => self::PASSWORD,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        // Full access to the seeded demo company, when there is one, so the
        // account lands on a working dashboard instead of the onboarding wizard.
        $company = Company::query()->where('code', 'CO-001')->first();
        if ($company === null) {
            $this->command?->info('No demo company (CO-001) — the account will start at onboarding.');

            return;
        }

        $access = app(AccessControl::class);
        foreach (ApiPermission::all() as $permission) {
            $access->grant($user, $company, $permission);
        }
        $access->grant($user, $company, 'journal.approve');
        $access->grant($user, $company, 'journal.post');
        $access->grant($user, $company, 'period.reopen');

        $this->command?->info('Test account ready: '.self::EMAIL);
    }
}
