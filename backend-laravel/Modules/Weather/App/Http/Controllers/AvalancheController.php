<?php

namespace Modules\Weather\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Weather\App\Data\UpsertAvalancheData;
use Modules\Weather\App\Models\WeatherAvalancheDaily;
use Modules\Weather\App\Services\AvalancheService;
use Modules\Weather\App\Support\WeatherAuthz;

class AvalancheController extends Controller
{
    public function __construct(private readonly AvalancheService $avalanche)
    {
    }

    public function index(): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessWeatherStaff()) {
            return $denied;
        }

        $rows = $this->avalanche->list();

        return response()->json([
            'results' => array_map(fn (WeatherAvalancheDaily $r) => $r->toApiArray(), $rows),
        ], 200);
    }

    public function upsert(UpsertAvalancheData $request): JsonResponse
    {
        if ($denied = WeatherAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        $row = $this->avalanche->upsert($request->toFillArray());
        $wasRecentlyCreated = $row->wasRecentlyCreated;

        return response()->json($row->toApiArray(), $wasRecentlyCreated ? 201 : 200);
    }
}
