<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Services\App\Models\Outing;
use Modules\Services\App\Models\OutingParticipant;
use Modules\Services\App\Services\OutingService;
use Modules\Services\App\Support\ServicesAuthz;

class OutingController extends Controller
{
    public function __construct(private readonly OutingService $outings)
    {
    }

    public function open(int $slot): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $outing = $this->outings->openForSlot($slot);
        $payload = $outing->toApiArray();
        $payload['participants'] = OutingParticipant::on(config('tenancy.connection_name'))
            ->where('outing_id', $outing->getKey())
            ->get()
            ->map(fn (OutingParticipant $p) => $p->toApiArray())
            ->all();

        return response()->json($payload, 201);
    }

    public function markOut(int $outing_participant): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $row = OutingParticipant::on(config('tenancy.connection_name'))->find($outing_participant);
        if ($row === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->outings->markOut($row)->toApiArray(), 200);
    }

    public function markBack(int $outing_participant): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $row = OutingParticipant::on(config('tenancy.connection_name'))->find($outing_participant);
        if ($row === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->outings->markBack($row)->toApiArray(), 200);
    }

    public function close(int $outing): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $model = Outing::on(config('tenancy.connection_name'))->find($outing);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->outings->close($model)->toApiArray(), 200);
    }
}
