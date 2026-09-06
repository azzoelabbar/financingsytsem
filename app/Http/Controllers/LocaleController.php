<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['ar', 'en'], true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        return back()->withCookie(cookie()->forever('locale', $locale));
    }
}
