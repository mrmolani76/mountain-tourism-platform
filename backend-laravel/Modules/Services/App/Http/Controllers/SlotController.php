<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Requests\AssignStaffRequest;
use Modules\Services\App\Http\Requests\StoreSlotRequest;
use Modules\Services\App\Models\Slot;
use Modules\Services\App\Models\SlotStaff;
use Modules\Services\App\Services\SlotService;
use Modules\Services\App\Support\ServicesAuthz;

class SlotController extends Controller
{
    public function __construct(private readonly SlotService $slots)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $query = Slot::on(config('tenancy.connection_name'))->orderBy('starts_at');
        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->query('service_id'));
        }

        $rows = $query->limit(100)->get()->map(fn (Slot $s) => $s->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreSlotRequest $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $slot = $this->slots->create($request->validated());

        return response()->json($slot->toApiArray(), 201);
    }

    public function assignStaff(AssignStaffRequest $request, int $slot): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $model = Slot::on(config('tenancy.connection_name'))->find($slot);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $rows = $this->slots->assignStaff($model, $request->validated('assignments'));

        return response()->json(['results' => $rows], 201);
    }

    public function staff(int $slot): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $rows = SlotStaff::on(config('tenancy.connection_name'))
            ->where('slot_id', $slot)
            ->get()
            ->map(fn (SlotStaff $s) => $s->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }
}
