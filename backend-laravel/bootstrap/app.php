<?php

use App\Http\Middleware\EnsureTenantResolved;
use App\Http\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\PreventAccessFromTenantDomains;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\StaffSessionAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SPA cookie session (Nuxt) — Sanctum stateful domains + CSRF.
        $middleware->statefulApi();

        // Session only on api; tenant resolve is on tenant module route groups
        // (EQMS/Nexro: SaaS central routes must not activate a tenant DB).
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
        $middleware->alias([
            'resolve.tenant' => ResolveTenant::class,
            'tenancy.prevent_central' => PreventAccessFromCentralDomains::class,
            'tenancy.prevent_tenant' => PreventAccessFromTenantDomains::class,
            'tenancy.ensure_resolved' => EnsureTenantResolved::class,
            'staff.auth' => StaffSessionAuth::class,
            // Inventory `auth:central` equivalent for SaaS session.
            'saas.auth' => \App\Http\Middleware\SaasSessionAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // DRF parity: serializer validation failures are HTTP 400, never 422.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'error',
                    'message' => 'Validation failed.',
                    'details' => $e->errors() === [] ? new \stdClass() : $e->errors(),
                ],
                'meta' => new \stdClass(),
            ], 400);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'error',
                    'message' => 'Not found.',
                    'details' => new \stdClass(),
                ],
                'meta' => new \stdClass(),
            ], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'error',
                    'message' => 'Method "' . $request->method() . '" not allowed.',
                    'details' => new \stdClass(),
                ],
                'meta' => new \stdClass(),
            ], 405);
        });
    })->create();
