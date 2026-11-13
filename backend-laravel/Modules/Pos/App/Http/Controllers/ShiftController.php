<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pos\App\Http\Requests\CloseShiftRequest;
use Modules\Pos\App\Http\Requests\OpenShiftRequest;
use Modules\Pos\App\Models\CashierShift;
use Modules\Pos\App\Services\ShiftService;
use Modules\Pos\App\Support\PosAuthz;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shifts)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $query = CashierShift::on(config('tenancy.connection_name'))->orderByDesc('id');
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', (int) $request->query('outlet_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $rows = $query->limit(50)->get()->map(fn (CashierShift $s) => $s->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function open(OpenShiftRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $data = $request->validated();
        $shift = $this->shifts->open(
            (int) $data['outlet_id'],
            (int) ($data['opening_float_cents'] ?? 0),
            $data['notes'] ?? null,
        );

        return response()->json($shift->toApiArray(), 201);
    }

    public function close(CloseShiftRequest $request, int $shift): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = CashierShift::on(config('tenancy.connection_name'))->find($shift);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $data = $request->validated();
        $closed = $this->shifts->close(
            $model,
            (int) $data['counted_cash_cents'],
            $data['notes'] ?? null,
        );

        return response()->json($closed->toApiArray(), 200);
    }
}
