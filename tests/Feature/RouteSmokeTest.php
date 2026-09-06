<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\AccountingReferenceSeeder;
use Database\Seeders\DemoCompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AccountingReferenceSeeder::class);
    $this->seed(DemoCompanySeeder::class);
    $this->seed(DemoUserSeeder::class);
    $this->actingAs(User::query()->where('email', 'test@example.com')->firstOrFail());
});

/**
 * Certification smoke test: every parameterless GET screen must render without
 * a server error (status < 500) for an authenticated user with company context.
 * Self-maintaining — it enumerates the router, so new routes are covered too.
 */
it('renders every parameterless GET screen without a server error', function () {
    $failures = [];
    $checked = 0;

    foreach (Route::getRoutes() as $route) {
        if (! in_array('GET', $route->methods(), true)) {
            continue;
        }
        if ($route->parameterNames() !== []) {
            continue; // parameterised routes are covered by dedicated workspace tests
        }

        $uri = $route->uri();
        // Skip framework/auth plumbing that isn't a navigable app screen.
        // passkeys/* and *ptions are WebAuthn ceremony XHR endpoints (auth package,
        // out of Phase AD scope) that require a live challenge, not HTML screens.
        if (in_array($uri, ['/', 'up'], true)
            || str_starts_with($uri, '_')
            || str_contains($uri, 'passkey')
            || str_ends_with($uri, '/options')) {
            continue;
        }

        $checked++;
        $status = $this->get('/'.ltrim($uri, '/'))->getStatusCode();
        if ($status >= 500) {
            $failures[] = $uri.' → '.$status;
        }
    }

    expect($checked)->toBeGreaterThan(30);
    expect($failures)->toBe([], 'Routes returned a server error: '.implode(', ', $failures));
});
