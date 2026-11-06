<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Data\StoreHutBedData;
use Modules\Hotel\App\Data\UpdateHutBedData;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class HutBedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = HutBed::on(config('tenancy.connection_name'))
            ->when($request->filled('hut_dormitory_id'), fn ($q) => $q->where('hut_dormitory_id', (int) $request->query('hut_dormitory_id')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('hut_dormitory_id')
            ->orderBy('bed_number');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (HutBed $bed) => $bed->toApiArray());
    }

    public function show(int $hut_bed): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = HutBed::on(config('tenancy.connection_name'))->find($hut_bed);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreHutBedData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutBed::on(config('tenancy.connection_name'))->create($request->toFillArray());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_hut_bed',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateHutBedData $request, int $hut_bed): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutBed::on(config('tenancy.connection_name'))->find($hut_bed);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($request->toFillArray())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_hut_bed',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $hut_bed): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = HutBed::on(config('tenancy.connection_name'))->find($hut_bed);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $inUse = ReservationAccommodation::on(config('tenancy.connection_name'))
            ->where('hut_bed_id', $model->getKey())
            ->exists();

        if ($inUse) {
            return Envelope::errorJson('in_use', 'Bed is referenced by reservations.', [], 422);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_hut_bed', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
