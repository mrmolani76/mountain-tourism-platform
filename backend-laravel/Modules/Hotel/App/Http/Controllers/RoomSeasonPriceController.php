<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Data\StoreRoomSeasonPriceData;
use Modules\Hotel\App\Data\UpdateRoomSeasonPriceData;
use Modules\Hotel\App\Models\RoomSeasonPrice;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class RoomSeasonPriceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = RoomSeasonPrice::on(config('tenancy.connection_name'))
            ->when($request->filled('room_type_id'), fn ($q) => $q->where('room_type_id', (int) $request->query('room_type_id')))
            ->when($request->filled('season_id'), fn ($q) => $q->where('season_id', (int) $request->query('season_id')))
            ->orderBy('room_type_id')
            ->orderBy('season_id');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (RoomSeasonPrice $price) => $price->toApiArray());
    }

    public function show(int $room_season_price): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = RoomSeasonPrice::on(config('tenancy.connection_name'))->find($room_season_price);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreRoomSeasonPriceData $data): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomSeasonPrice::on(config('tenancy.connection_name'))->create($data->toFillArray());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_room_season_price',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateRoomSeasonPriceData $data, int $room_season_price): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomSeasonPrice::on(config('tenancy.connection_name'))->find($room_season_price);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($data->toFillArray())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_room_season_price',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $room_season_price): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = RoomSeasonPrice::on(config('tenancy.connection_name'))->find($room_season_price);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_room_season_price', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
