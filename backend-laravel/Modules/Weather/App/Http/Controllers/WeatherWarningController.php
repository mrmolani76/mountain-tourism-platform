<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Weather\App\Models\WeatherWarning;
use Modules\Weather\App\Services\WeatherWarningService;
use Modules\Weather\App\Support\WeatherAuthz;

class WeatherWarningController extends Controller
{
    public function __construct(private readonly WeatherWarningService $warnings)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $status = $request->query('status', WeatherWarning::STATUS_ACTIVE);
        $rows = $this->warnings->list(is_string($status) ? $status : WeatherWarning::STATUS_ACTIVE);

        return response()->json([
            'results' => array_map(fn (WeatherWarning $w) => $w->toApiArray(), $rows),
        ], 200);
    }

    public function resolve(int $warning): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = WeatherWarning::on(config('tenancy.connection_name'))->find($warning);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model = $this->warnings->resolve($model);

        return response()->json($model->toApiArray(), 200);
    }
}
