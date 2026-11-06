<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Data\StoreBoardPlanData;
use Modules\Hotel\App\Data\UpdateBoardPlanData;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class BoardPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = BoardPlan::on(config('tenancy.connection_name'))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('code');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (BoardPlan $plan) => $plan->toApiArray());
    }

    public function show(int $board_plan): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = BoardPlan::on(config('tenancy.connection_name'))->find($board_plan);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreBoardPlanData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = BoardPlan::on(config('tenancy.connection_name'))->create($request->toFillArray());
        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'hotel_board_plan',
            (int) $model->getKey(),
            newValues: $model->toApiArray(),
        );

        return response()->json($model->fresh()->toApiArray(), 201);
    }

    public function update(UpdateBoardPlanData $request, int $board_plan): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = BoardPlan::on(config('tenancy.connection_name'))->find($board_plan);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $old = $model->toApiArray();
        $model->fill($request->toFillArray())->save();
        $fresh = $model->fresh();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_board_plan',
            (int) $fresh->getKey(),
            oldValues: $old,
            newValues: $fresh->toApiArray(),
        );

        return response()->json($fresh->toApiArray(), 200);
    }

    public function destroy(int $board_plan): JsonResponse|\Illuminate\Http\Response
    {
        if ($denied = HotelAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $model = BoardPlan::on(config('tenancy.connection_name'))->find($board_plan);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $inUse = Reservation::on(config('tenancy.connection_name'))
            ->where('board_plan_id', $model->getKey())
            ->exists();

        if ($inUse) {
            return Envelope::errorJson('in_use', 'Board plan is referenced by reservations.', [], 422);
        }

        $old = $model->toApiArray();
        $id = (int) $model->getKey();
        $model->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'hotel_board_plan', $id, oldValues: $old, reason: 'deleted');

        return response()->noContent();
    }
}
