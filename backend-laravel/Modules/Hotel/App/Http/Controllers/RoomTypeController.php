<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Data\StoreRoomTypeData;
use Modules\Hotel\App\Data\UpdateRoomTypeData;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\RoomType;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class RoomTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = RoomType::on(config('tenancy.connection_name'))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (RoomType $type) => $type->toApiArray());
    }

    public function show(int $room_type): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = RoomType::on(config('tenancy.connection_name'))->find($room_type);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreRoomTypeData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomType::on(config('tenancy.connection_name'))->create($request->toFillArray());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_room_type',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateRoomTypeData $request, int $room_type): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomType::on(config('tenancy.connection_name'))->find($room_type);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($request->toFillArray())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_room_type',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $room_type): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomType::on(config('tenancy.connection_name'))->find($room_type);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $inUse = Room::on(config('tenancy.connection_name'))->where('room_type_id', $model->getKey())->exists()
            || HutDormitory::on(config('tenancy.connection_name'))->where('room_type_id', $model->getKey())->exists();

        if ($inUse) {
            return Envelope::errorJson('in_use', 'Room type is used by rooms or dormitories.', [], 422);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_room_type', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
