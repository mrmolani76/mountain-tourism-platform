<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Public health check. Mirrors Django GET /api/health/ semantics:
     * no auth required, envelope response, DB status reported.
     */
    public function __invoke(): JsonResponse
    {
        $dbHealthy = true;

        try {
            DB::connection()->getPdo()->query('select 1');
        } catch (\Throwable $e) {
            $dbHealthy = false;
        }

        return response()->api([
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'database' => $dbHealthy ? 'ok' : 'error',
            'time' => now()->toIso8601String(),
        ]);
    }
}
