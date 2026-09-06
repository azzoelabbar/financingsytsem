<?php

use App\Http\Middleware\EnsureRegistrationIsOpen;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\SetLocale;
use App\Services\Accounting\Exceptions\PostingException;
use App\Services\Accounting\Integrity\Exceptions\IntegrityViolationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'locale']);

        $middleware->web(append: [
            HandleAppearance::class,
            SetLocale::class,
            EnsureRegistrationIsOpen::class,
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // The container is reached through a tunnel or reverse proxy when the
        // system is shared by link, so honour the forwarded scheme and host —
        // without this, links and redirects come back as plain http://localhost.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'forbidden',
                        'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Forbidden.',
                    ],
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'not_found',
                        'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Not found.',
                    ],
                ], 404);
            }
        });

        $exceptions->render(function (PostingException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'business_rule',
                        'message' => $e->getMessage(),
                    ],
                ], 422);
            }
        });

        $exceptions->render(function (IntegrityViolationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'integrity_violation',
                        'message' => $e->getMessage(),
                    ],
                ], 422);
            }
        });
    })->create();
