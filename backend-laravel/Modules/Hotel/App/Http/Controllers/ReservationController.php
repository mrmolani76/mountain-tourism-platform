<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hotel\App\Data\CancelReservationData;
use Modules\Hotel\App\Data\StoreReservationData;
use Modules\Hotel\App\Data\UpdateReservationData;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Services\CheckInService;
use Modules\Hotel\App\Services\ReservationService;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;
use Modules\Folio\App\Models\Folio;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservations,
        private readonly CheckInService $checkIn,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = Reservation::on(config('tenancy.connection_name'))
            ->with('allocations')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('guest'), fn ($q) => $q->where('guest_name', 'like', '%'.$request->query('guest').'%'))
            ->when($request->filled('from'), fn ($q) => $q->where('check_out_on', '>', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('check_in_on', '<', $request->query('to')))
            ->orderByDesc('check_in_on')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (Reservation $reservation) => $reservation->toApiArray());
    }

    public function show(int $reservation): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Reservation::on(config('tenancy.connection_name'))
            ->with('allocations')
            ->find($reservation);

        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function store(StoreReservationData $data): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $reservation = $this->reservations->create($data->toFillArray());

        return response()->json($reservation->toApiArray(), 201);
    }

    public function update(UpdateReservationData $data, int $reservation): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Reservation::on(config('tenancy.connection_name'))->find($reservation);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->reservations->update($model, $data->toFillArray())->toApiArray(), 200);
    }

    public function cancel(CancelReservationData $data, int $reservation): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Reservation::on(config('tenancy.connection_name'))->find($reservation);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $cancelled = $this->reservations->cancel($model, $data->reason);

        return response()->json($cancelled->toApiArray(), 200);
    }

    public function checkIn(int $reservation): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Reservation::on(config('tenancy.connection_name'))->find($reservation);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $stay = $this->checkIn->checkIn($model);
        $folio = $stay->getAttribute('folio_id') === null
            ? null
            : Folio::on(config('tenancy.connection_name'))->find((int) $stay->getAttribute('folio_id'));

        return response()->json([
            'stay' => $stay->toApiArray(),
            'folio' => $folio?->toApiArray(),
        ], 201);
    }
}
