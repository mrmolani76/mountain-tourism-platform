<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Requests\StoreBookingRequest;
use Modules\Services\App\Models\Booking;
use Modules\Services\App\Models\BookingParticipant;
use Modules\Services\App\Services\BookingService;
use Modules\Services\App\Support\ServicesAuthz;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $query = Booking::on(config('tenancy.connection_name'))->orderByDesc('id');
        if ($request->filled('slot_id')) {
            $query->where('slot_id', (int) $request->query('slot_id'));
        }

        $rows = $query->limit(50)->get()->map(fn (Booking $b) => $b->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $booking = $this->bookings->book($request->validated());
        $payload = $booking->toApiArray();
        $payload['participants'] = BookingParticipant::on(config('tenancy.connection_name'))
            ->where('booking_id', $booking->getKey())
            ->get()
            ->map(fn (BookingParticipant $p) => $p->toApiArray())
            ->all();

        return response()->json($payload, 201);
    }

    public function cancel(int $booking): JsonResponse
    {
        if ($denied = ServicesAuthz::denyUnlessServiceDesk()) {
            return $denied;
        }

        $model = Booking::on(config('tenancy.connection_name'))->find($booking);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $cancelled = $this->bookings->cancel($model);

        return response()->json($cancelled->toApiArray(), 200);
    }
}
