<?php

namespace App\Providers;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Saas\App\Models\Domain;
use PDOException;

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
        $this->mergeRegisteredTenantHostsIntoSanctum();

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

    private function mergeRegisteredTenantHostsIntoSanctum(): void
    {
        $connection = (string) config('tenancy.central_connection', config('database.default'));

        try {
            if (! Schema::connection($connection)->hasTable('saas_domain')) {
                return;
            }

            $hosts = Domain::query()
                ->pluck('hostname')
                ->filter()
                ->map(fn (string $host): string => trim($host))
                ->filter()
                ->values();
        } catch (QueryException|PDOException $exception) {
            Log::warning('Registered tenant hosts could not be added to Sanctum stateful domains.', [
                'exception_class' => get_class($exception),
            ]);

            return;
        }

        $stateful = (array) config('sanctum.stateful', []);
        $frontendPort = (int) config('sanctum.frontend_port', 3000);

        foreach ($hosts as $host) {
            $stateful[] = $host;
            if ($frontendPort > 0) {
                $stateful[] = "{$host}:{$frontendPort}";
            }
        }

        config(['sanctum.stateful' => array_values(array_unique($stateful))]);
    }
}
