<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Services\App\Http\Requests\StoreServiceRequest;
use Modules\Services\App\Models\ServiceDefinition;
use Modules\Services\App\Support\ServicesAuthz;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $rows = ServiceDefinition::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceDefinition $s) => $s->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $service = ServiceDefinition::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($service->toApiArray(), 201);
    }

    public function update(StoreServiceRequest $request, int $service): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = ServiceDefinition::on(config('tenancy.connection_name'))->find($service);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
