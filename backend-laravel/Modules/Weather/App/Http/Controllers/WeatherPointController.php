<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Weather\App\Data\StoreWeatherPointData;
use Modules\Weather\App\Data\UpdateWeatherPointData;
use Modules\Weather\App\Models\WeatherPoint;
use Modules\Weather\App\Services\WeatherEvaluateService;
use Modules\Weather\App\Services\WeatherFetchService;
use Modules\Weather\App\Services\WeatherPointService;
use Modules\Weather\App\Support\WeatherAuthz;

class WeatherPointController extends Controller
{
    public function __construct(
        private readonly WeatherPointService $points,
        private readonly WeatherFetchService $fetch,
        private readonly WeatherEvaluateService $evaluate,
    ) {
    }

    public function index(): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $rows = WeatherPoint::on(config('tenancy.connection_name'))
            ->orderBy('name')
            ->get()
            ->map(fn (WeatherPoint $p) => $p->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreWeatherPointData $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        $point = $this->points->create($request->toFillArray());

        return response()->json($point->toApiArray(), 201);
    }

    public function update(UpdateWeatherPointData $request, int $point): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        $model = WeatherPoint::on(config('tenancy.connection_name'))->find($point);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model = $this->points->update($model, $request->toFillArray());

        return response()->json($model->toApiArray(), 200);
    }

    public function fetch(int $point): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $model = WeatherPoint::on(config('tenancy.connection_name'))->find($point);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $result = $this->fetch->fetch($model);
        $status = $result['run']->getAttribute('status') === 'success' ? 201 : 200;

        return response()->json([
            'run' => $result['run']->toApiArray(),
            'snapshot' => $result['snapshot']?->toApiArray(),
            'kept_previous' => (bool) $result['kept_previous'],
            'provider' => $result['provider'] ?? null,
            'attribution' => $result['attribution'] ?? config('weather.attribution'),
        ], $status);
    }

    public function evaluate(int $point): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $model = WeatherPoint::on(config('tenancy.connection_name'))->find($point);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $result = $this->evaluate->evaluate($model);

        return response()->json([
            'created' => $result['created'],
            'updated' => $result['updated'],
            'warnings' => array_map(
                fn ($w) => $w->toApiArray(),
                $result['warnings']
            ),
        ], 200);
    }
}
