<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Registration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * The public landing page.
 *
 * A fresh install has no account yet, so the first visitor is sent straight to
 * the one-time registration form. Once the owner account exists, registration
 * is closed and this page offers sign-in only.
 */
class HomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (Registration::isOpen() && Auth::guest()) {
            return redirect()->route('register');
        }

        return view('welcome');
    }
}
