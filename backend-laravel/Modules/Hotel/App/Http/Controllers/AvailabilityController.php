<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Hotel\App\Data\AvailabilityData;
use Modules\Hotel\App\Data\CalendarData;
use Modules\Hotel\App\Data\QuoteData;
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

    public function index(AvailabilityData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => $this->availability->availability(
                $request->check_in_on,
                $request->check_out_on,
                $request->building_id
            ),
            'error' => null,
            'meta' => [],
        ], 200);
    }

    public function calendar(CalendarData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'from' => $request->from,
                'to' => $request->to,
                'building_id' => $request->building_id,
                'allocations' => $this->availability->calendar(
                    $request->building_id,
                    $request->from,
                    $request->to
                ),
            ],
            'error' => null,
            'meta' => [],
        ], 200);
    }

    public function quote(QuoteData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $roomType = RoomType::on(config('tenancy.connection_name'))->find($request->room_type_id);
        if ($roomType === null) {
            return Envelope::errorJson('not_found', 'Room type not found.', [], 404);
        }

        $boardPlan = $request->board_plan_id !== null
            ? BoardPlan::on(config('tenancy.connection_name'))->find($request->board_plan_id)
            : null;

        $quote = $this->pricing->quote(
            $roomType,
            $boardPlan,
            $request->check_in_on,
            $request->check_out_on,
            $request->adults,
            (int) ($request->children ?? 0)
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
