<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Transport\App\Http\Requests\StoreRouteRequest;
use Modules\Transport\App\Models\RouteStop;
use Modules\Transport\App\Models\TransportRoute;
use Modules\Transport\App\Support\TransportAuthz;

class RouteController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $rows = TransportRoute::on(config('tenancy.connection_name'))
            ->with('stops')
            ->orderBy('id')
            ->get()
            ->map(fn (TransportRoute $r) => $r->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreRouteRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $data = $request->validated();
        $stops = $data['stops'] ?? [];
        unset($data['stops']);

        $route = DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data, $stops) {
            $route = TransportRoute::on(config('tenancy.connection_name'))->create($data);
            foreach ($stops as $stop) {
                RouteStop::on(config('tenancy.connection_name'))->create([
                    'route_id' => $route->getKey(),
                    'sequence' => $stop['sequence'],
                    'name' => $stop['name'],
                    'building_id' => $stop['building_id'] ?? null,
                    'latitude' => $stop['latitude'] ?? null,
                    'longitude' => $stop['longitude'] ?? null,
                ]);
            }

            return $route->load('stops');
        });

        return response()->json($route->toApiArray(), 201);
    }

    public function update(StoreRouteRequest $request, int $route): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = TransportRoute::on(config('tenancy.connection_name'))->find($route);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $data = $request->validated();
        $stops = $data['stops'] ?? null;
        unset($data['stops']);

        $updated = DB::connection(config('tenancy.connection_name'))->transaction(function () use ($model, $data, $stops) {
            $model->fill($data);
            $model->save();
            if (is_array($stops)) {
                RouteStop::on(config('tenancy.connection_name'))
                    ->where('route_id', $model->getKey())
                    ->delete();
                foreach ($stops as $stop) {
                    RouteStop::on(config('tenancy.connection_name'))->create([
                        'route_id' => $model->getKey(),
                        'sequence' => $stop['sequence'],
                        'name' => $stop['name'],
                        'building_id' => $stop['building_id'] ?? null,
                        'latitude' => $stop['latitude'] ?? null,
                        'longitude' => $stop['longitude'] ?? null,
                    ]);
                }
            }

            return $model->load('stops');
        });

        return response()->json($updated->toApiArray(), 200);
    }
}
