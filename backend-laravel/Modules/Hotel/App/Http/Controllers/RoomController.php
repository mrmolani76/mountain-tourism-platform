<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Http\Requests\StoreRoomRequest;
use Modules\Hotel\App\Http\Requests\UpdateRoomRequest;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = Room::on(config('tenancy.connection_name'))
            ->when($request->filled('building_id'), fn ($q) => $q->where('building_id', (int) $request->query('building_id')))
            ->when($request->filled('room_type_id'), fn ($q) => $q->where('room_type_id', (int) $request->query('room_type_id')))
            ->when($request->filled('accommodation_type'), fn ($q) => $q->where('accommodation_type', $request->query('accommodation_type')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('building_id')
            ->orderBy('room_number');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (Room $room) => $room->toApiArray());
    }

    public function show(int $room): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Room::on(config('tenancy.connection_name'))->find($room);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = Room::on(config('tenancy.connection_name'))->create($request->validated());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_room',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateRoomRequest $request, int $room): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = Room::on(config('tenancy.connection_name'))->find($room);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($request->validated())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_room',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $room): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = Room::on(config('tenancy.connection_name'))->find($room);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $inUse = ReservationAccommodation::on(config('tenancy.connection_name'))
            ->where('room_id', $model->getKey())
            ->exists();

        if ($inUse) {
            return Envelope::errorJson('in_use', 'Room is referenced by reservations.', [], 422);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_room', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
