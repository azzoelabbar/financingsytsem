<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale')
            ?? session('locale')
            ?? 'ar';

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        $request->attributes->set('locale', $locale);
        $request->attributes->set('dir', $locale === 'ar' ? 'rtl' : 'ltr');

        return $next($request);
    }
}
