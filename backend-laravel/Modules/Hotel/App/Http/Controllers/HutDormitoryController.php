<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Data\StoreHutDormitoryData;
use Modules\Hotel\App\Data\UpdateHutDormitoryData;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class HutDormitoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = HutDormitory::on(config('tenancy.connection_name'))
            ->when($request->filled('building_id'), fn ($q) => $q->where('building_id', (int) $request->query('building_id')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('building_id')
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (HutDormitory $dorm) => $dorm->toApiArray());
    }

    public function show(int $hut_dormitory): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = HutDormitory::on(config('tenancy.connection_name'))->find($hut_dormitory);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreHutDormitoryData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutDormitory::on(config('tenancy.connection_name'))->create($request->toFillArray());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_hut_dormitory',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateHutDormitoryData $request, int $hut_dormitory): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutDormitory::on(config('tenancy.connection_name'))->find($hut_dormitory);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($request->toFillArray())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_hut_dormitory',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $hut_dormitory): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutDormitory::on(config('tenancy.connection_name'))->find($hut_dormitory);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $bedIds = HutBed::on(config('tenancy.connection_name'))
            ->where('hut_dormitory_id', $model->getKey())
            ->pluck('id')
            ->all();

        if ($bedIds !== [] && ReservationAccommodation::on(config('tenancy.connection_name'))
            ->whereIn('hut_bed_id', $bedIds)
            ->exists()) {
            return Envelope::errorJson('in_use', 'Dormitory beds are referenced by reservations.', [], 422);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_hut_dormitory', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
