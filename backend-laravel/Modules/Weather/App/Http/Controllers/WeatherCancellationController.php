<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Weather\App\Data\StoreWeatherCancellationData;
use Modules\Weather\App\Services\WeatherCancellationService;
use Modules\Weather\App\Support\WeatherAuthz;

class WeatherCancellationController extends Controller
{
    public function __construct(private readonly WeatherCancellationService $cancellations)
    {
    }

    public function store(StoreWeatherCancellationData $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $result = $this->cancellations->cancel($request->toFillArray());

        return response()->json([
            'cancellation' => $result['cancellation']->toApiArray(),
            'voids' => $result['voids'],
            'guests_to_notify' => $result['guests_to_notify'],
        ], 201);
    }
}
