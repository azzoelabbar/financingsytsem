<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Registration;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes the registration routes once the install has its one account.
 *
 * Sits in the web group but only acts on Fortify's `register` routes (the form
 * and the submission), so the check costs nothing on every other request.
 */
class EnsureRegistrationIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('register', 'register.store') || Registration::isOpen()) {
            return $next($request);
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login')
            ->with('status', __('erp.auth.registration_closed'));
    }
}
