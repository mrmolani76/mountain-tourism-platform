<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Transport\App\Http\Requests\ChangeLiftStatusRequest;
use Modules\Transport\App\Http\Requests\StoreLiftRequest;
use Modules\Transport\App\Models\Lift;
use Modules\Transport\App\Models\LiftStatusHistory;
use Modules\Transport\App\Services\LiftService;
use Modules\Transport\App\Support\TransportAuthz;

class LiftController extends Controller
{
    public function __construct(private readonly LiftService $lifts)
    {
    }

    public function index(): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessLiftOperator()) {
            return $denied;
        }

        $rows = Lift::on(config('tenancy.connection_name'))
            ->orderBy('name')
            ->get()
            ->map(fn (Lift $l) => $l->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreLiftRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $lift = $this->lifts->create($request->validated());

        return response()->json($lift->toApiArray(), 201);
    }

    public function update(StoreLiftRequest $request, int $lift): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Lift::on(config('tenancy.connection_name'))->find($lift);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }

    public function changeStatus(ChangeLiftStatusRequest $request, int $lift): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessLiftOperator()) {
            return $denied;
        }

        $model = Lift::on(config('tenancy.connection_name'))->find($lift);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $history = $this->lifts->changeStatus($model, $request->validated());

        return response()->json([
            'lift' => $model->fresh()->toApiArray(),
            'history' => $history->toApiArray(),
        ], 201);
    }

    public function history(int $lift): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessLiftOperator()) {
            return $denied;
        }

        $model = Lift::on(config('tenancy.connection_name'))->find($lift);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $rows = LiftStatusHistory::on(config('tenancy.connection_name'))
            ->where('lift_id', $model->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (LiftStatusHistory $h) => $h->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function lobby(): JsonResponse
    {
        return response()->json(['results' => $this->lifts->lobbyStatus()], 200);
    }
}
