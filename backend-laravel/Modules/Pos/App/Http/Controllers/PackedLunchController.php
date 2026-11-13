<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pos\App\Http\Requests\StorePackedLunchRequest;
use Modules\Pos\App\Models\PackedLunch;
use Modules\Pos\App\Models\PackedLunchItem;
use Modules\Pos\App\Services\PackedLunchService;
use Modules\Pos\App\Support\PosAuthz;

class PackedLunchController extends Controller
{
    public function __construct(private readonly PackedLunchService $lunches)
    {
    }

    public function store(StorePackedLunchRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $lunch = $this->lunches->create($request->validated());
        $payload = $lunch->toApiArray();
        $payload['items'] = PackedLunchItem::on(config('tenancy.connection_name'))
            ->where('packed_lunch_id', $lunch->getKey())
            ->get()
            ->map(fn (PackedLunchItem $i) => $i->toApiArray())
            ->all();

        return response()->json($payload, 201);
    }

    public function kitchenList(Request $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $forDate = $request->query('for_date');
        if (! is_string($forDate) || $forDate === '') {
            return Envelope::errorJson('for_date_required', 'Query for_date is required (Y-m-d).', [], 400);
        }

        $outletId = $request->filled('outlet_id') ? (int) $request->query('outlet_id') : null;
        $rows = $this->lunches->kitchenList($forDate, $outletId);

        return response()->json(['for_date' => $forDate, 'results' => $rows], 200);
    }

    public function updateStatus(Request $request, int $packed_lunch): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = PackedLunch::on(config('tenancy.connection_name'))->find($packed_lunch);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $status = $request->input('status');
        if (! is_string($status)) {
            return Envelope::errorJson('status_required', 'status is required.', [], 400);
        }

        $updated = $this->lunches->updateStatus($model, $status);

        return response()->json($updated->toApiArray(), 200);
    }
}
