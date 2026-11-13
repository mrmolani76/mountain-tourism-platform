<?php

namespace Modules\Rental\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Rental\App\Http\Requests\StoreEquipmentItemRequest;
use Modules\Rental\App\Models\EquipmentItem;
use Modules\Rental\App\Support\RentalAuthz;

class EquipmentItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $query = EquipmentItem::on(config('tenancy.connection_name'))->orderBy('id');
        if ($request->filled('equipment_type_id')) {
            $query->where('equipment_type_id', (int) $request->query('equipment_type_id'));
        }

        $rows = $query->get()->map(fn (EquipmentItem $i) => $i->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreEquipmentItemRequest $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $item = EquipmentItem::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($item->toApiArray(), 201);
    }

    public function update(StoreEquipmentItemRequest $request, int $equipment_item): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = EquipmentItem::on(config('tenancy.connection_name'))->find($equipment_item);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
