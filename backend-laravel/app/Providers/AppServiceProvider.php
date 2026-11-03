<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Registers the platform API envelope macros. The existing Vue/Nuxt
     * frontend was built against the DRF contract (see
     * docs/LARAVEL_SWITCH_AUDIT.md §2 / backend/common/responses) and MUST
     * keep working while Laravel replaces Django/DRF:
     *
     *     success: {success: true,  data: ..., error: null, meta: {...}}
     *     error:   {success: false, data: null,
     *               error: {code, message, details}, meta: {...}}
     */
    public function boot(): void
    {
        Response::macro('api', function (mixed $data = null, array $meta = [], int $status = 200, string $message = ''): JsonResponse {
            return response()->json([
                'success' => true,
                ...(($message !== '') ? ['message' => $message] : []),
                'data' => $data,
                'error' => null,
                'meta' => $meta === [] ? new \stdClass() : $meta,
            ], $status);
        });

        Response::macro('apiError', function (string $code, string $message, array $details = [], int $status = 400): JsonResponse {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => $details === [] ? new \stdClass() : $details,
                ],
                'meta' => new \stdClass(),
            ], $status);
        });
    }
}
