<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Transport\App\Http\Requests\StoreVehicleRequest;
use Modules\Transport\App\Models\Vehicle;
use Modules\Transport\App\Support\TransportAuthz;

class VehicleController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $rows = Vehicle::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (Vehicle $v) => $v->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $vehicle = Vehicle::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($vehicle->toApiArray(), 201);
    }

    public function update(StoreVehicleRequest $request, int $vehicle): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Vehicle::on(config('tenancy.connection_name'))->find($vehicle);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
