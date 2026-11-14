<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Weather\App\Data\StoreWeatherLimitData;
use Modules\Weather\App\Data\UpdateWeatherLimitData;
use Modules\Weather\App\Models\WeatherLimit;
use Modules\Weather\App\Services\WeatherLimitService;
use Modules\Weather\App\Support\WeatherAuthz;

class WeatherLimitController extends Controller
{
    public function __construct(private readonly WeatherLimitService $limits)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $query = WeatherLimit::on(config('tenancy.connection_name'))->orderBy('id');

        if ($request->filled('weather_point_id')) {
            $query->where('weather_point_id', (int) $request->query('weather_point_id'));
        }

        $rows = $query->get()->map(fn (WeatherLimit $l) => $l->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreWeatherLimitData $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        $limit = $this->limits->create($request->toFillArray());

        return response()->json($limit->toApiArray(), 201);
    }

    public function update(UpdateWeatherLimitData $request, int $limit): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        $model = WeatherLimit::on(config('tenancy.connection_name'))->find($limit);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model = $this->limits->update($model, $request->toFillArray());

        return response()->json($model->toApiArray(), 200);
    }
}
