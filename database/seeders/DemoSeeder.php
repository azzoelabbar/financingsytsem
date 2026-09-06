<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * A demo workspace for development: a company, a full chart of accounts, an
 * operator account (test@example.com / password) and sample transactions.
 *
 *     php artisan db:seed --class=DemoSeeder
 *
 * Never part of `migrate:fresh --seed`. This install allows exactly one
 * account, and the demo operator would take it — which would close
 * registration before the real owner ever saw the form.
 */
class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'DemoSeeder must never run in production: the demo operator would take the single account this install allows.'
            );
        }

        $this->call([
            AccountingReferenceSeeder::class,
            DemoCompanySeeder::class,
            DemoUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
