<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Hotel\App\Http\Requests\AvailabilityRequest;
use Modules\Hotel\App\Http\Requests\CalendarRequest;
use Modules\Hotel\App\Http\Requests\QuoteRequest;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\RoomType;
use Modules\Hotel\App\Services\AvailabilityService;
use Modules\Hotel\App\Services\PricingService;
use Modules\Hotel\App\Support\HotelAuthz;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {
    }

    public function index(AvailabilityRequest $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $data = $request->validated();
        $buildingId = isset($data['building_id']) ? (int) $data['building_id'] : null;

        return response()->json([
            'success' => true,
            'data' => $this->availability->availability(
                $data['check_in_on'],
                $data['check_out_on'],
                $buildingId
            ),
            'error' => null,
            'meta' => [],
        ], 200);
    }

    public function calendar(CalendarRequest $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $data = $request->validated();
        $buildingId = isset($data['building_id']) ? (int) $data['building_id'] : null;

        return response()->json([
            'success' => true,
            'data' => [
                'from' => $data['from'],
                'to' => $data['to'],
                'building_id' => $buildingId,
                'allocations' => $this->availability->calendar($buildingId, $data['from'], $data['to']),
            ],
            'error' => null,
            'meta' => [],
        ], 200);
    }

    public function quote(QuoteRequest $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $data = $request->validated();

        $roomType = RoomType::on(config('tenancy.connection_name'))->find((int) $data['room_type_id']);
        if ($roomType === null) {
            return Envelope::errorJson('not_found', 'Room type not found.', [], 404);
        }

        $boardPlan = isset($data['board_plan_id'])
            ? BoardPlan::on(config('tenancy.connection_name'))->find((int) $data['board_plan_id'])
            : null;

        $quote = $this->pricing->quote(
            $roomType,
            $boardPlan,
            $data['check_in_on'],
            $data['check_out_on'],
            (int) $data['adults'],
            (int) ($data['children'] ?? 0)
        );

        return response()->json([
            'success' => true,
            'data' => array_merge($quote, [
                'room_type_id' => (int) $roomType->getKey(),
                'board_plan_id' => $boardPlan?->getKey(),
            ]),
            'error' => null,
            'meta' => [],
        ], 200);
    }
}
