<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Accounting\AccountingContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the ERP: an authenticated user with no company (no AccessGrant) has
 * not completed onboarding and is redirected into the onboarding flow. Users
 * who already have a company (incl. seeded/demo users) pass straight through.
 */
class EnsureOnboarded
{
    public function __construct(private readonly AccountingContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $this->context->companiesFor($user)->isEmpty()) {
            return redirect()->route('onboarding.company');
        }

        return $next($request);
    }
}
