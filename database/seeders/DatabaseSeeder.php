<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * What every install needs, and nothing more.
 *
 * `php artisan migrate:fresh --seed` must leave the system in the state a new
 * install expects: reference data in place, and **no user accounts at all**, so
 * the first visitor still gets the one-time registration form.
 *
 * The demo company, demo operator and sample transactions are deliberately not
 * here — creating a user would take the single account this install allows.
 * Ask for them explicitly when you want them:
 *
 *     php artisan db:seed --class=DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AccountingReferenceSeeder::class,
        ]);
    }
}
