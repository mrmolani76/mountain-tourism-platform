<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Transport\App\Http\Requests\StoreDriverRequest;
use Modules\Transport\App\Models\Driver;
use Modules\Transport\App\Support\TransportAuthz;

class DriverController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $rows = Driver::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (Driver $d) => $d->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreDriverRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $driver = Driver::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($driver->toApiArray(), 201);
    }

    public function update(StoreDriverRequest $request, int $driver): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Driver::on(config('tenancy.connection_name'))->find($driver);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
