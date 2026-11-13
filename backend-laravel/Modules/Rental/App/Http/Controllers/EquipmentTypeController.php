<?php

namespace Modules\Rental\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Rental\App\Http\Requests\StoreEquipmentTypeRequest;
use Modules\Rental\App\Models\EquipmentType;
use Modules\Rental\App\Support\RentalAuthz;

class EquipmentTypeController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $rows = EquipmentType::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (EquipmentType $t) => $t->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreEquipmentTypeRequest $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $type = EquipmentType::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($type->toApiArray(), 201);
    }

    public function update(StoreEquipmentTypeRequest $request, int $equipment_type): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = EquipmentType::on(config('tenancy.connection_name'))->find($equipment_type);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
