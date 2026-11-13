<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Transport\App\Http\Requests\PickupFromReservationRequest;
use Modules\Transport\App\Http\Requests\StorePassengerRequest;
use Modules\Transport\App\Http\Requests\StoreTripRequest;
use Modules\Transport\App\Models\Trip;
use Modules\Transport\App\Models\TripPassenger;
use Modules\Transport\App\Services\TripService;
use Modules\Transport\App\Support\TransportAuthz;

class TripController extends Controller
{
    public function __construct(private readonly TripService $trips)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $query = Trip::on(config('tenancy.connection_name'))->orderByDesc('starts_at');
        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }
        if ($request->filled('trip_type')) {
            $query->where('trip_type', (string) $request->query('trip_type'));
        }

        $rows = $query->limit(100)->get()->map(fn (Trip $t) => $t->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $trip = $this->trips->create($request->validated());

        return response()->json($trip->toApiArray(), 201);
    }

    public function show(int $trip): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $model = Trip::on(config('tenancy.connection_name'))->find($trip);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function pickupFromReservation(PickupFromReservationRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $trip = $this->trips->pickupFromReservation($request->validated());
        $payload = $trip->toApiArray();
        $payload['passengers'] = TripPassenger::on(config('tenancy.connection_name'))
            ->where('trip_id', $trip->getKey())
            ->get()
            ->map(fn (TripPassenger $p) => $p->toApiArray())
            ->all();

        return response()->json($payload, 201);
    }

    public function addPassenger(StorePassengerRequest $request, int $trip): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $model = Trip::on(config('tenancy.connection_name'))->find($trip);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $passenger = $this->trips->bookPassenger($model, $request->validated());

        return response()->json($passenger->toApiArray(), 201);
    }

    public function cancelPassenger(int $passenger): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $model = TripPassenger::on(config('tenancy.connection_name'))->find($passenger);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $cancelled = $this->trips->cancelPassenger($model);

        return response()->json($cancelled->toApiArray(), 200);
    }

    public function passengerList(int $trip): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessTransportDesk()) {
            return $denied;
        }

        $model = Trip::on(config('tenancy.connection_name'))->find($trip);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->trips->passengerList($model), 200);
    }
}
