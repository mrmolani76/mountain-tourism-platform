<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Hotel\App\Data\AvailabilityData;
use Modules\Hotel\App\Data\CalendarData;
use Modules\Hotel\App\Data\QuoteData;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\HutDormitory;
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

    /**
     * EQMS DataTable configs for availability result tables.
     * GET /api/hotel/availability/list/config
     */
    public function listingConfig(): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $roomField = static fn (string $title, string $key): array => [
            'title' => $title,
            'key' => $key,
            'db_field' => $key,
            'orderByField' => $key,
            'default' => true,
            'search_field' => 'Text',
            'column_sort' => true,
            'includeAllFilter' => false,
        ];

        $roomsHeaders = [
            'fields' => [
                $roomField('Room', 'room_number'),
                $roomField('Name', 'name'),
                $roomField('Room type', 'room_type'),
                $roomField('Type', 'accommodation_type'),
                array_merge($roomField('Availability', 'availability'), ['search_field' => 'Dropdown']),
            ],
            'default_sort' => ['key' => 'room_number', 'order' => 'asc'],
        ];

        $bedsHeaders = [
            'fields' => [
                $roomField('Dormitory', 'dormitory'),
                $roomField('Bed', 'bed_number'),
                array_merge($roomField('Availability', 'availability'), ['search_field' => 'Dropdown']),
            ],
            'default_sort' => ['key' => 'bed_number', 'order' => 'asc'],
        ];

        $emptyExtras = [
            'bulkActions' => [],
            'columnSearchable' => [],
            'showCheckbox' => false,
            'selectedColumns' => [],
            'showAddButton' => false,
            'advanceFilters' => new \stdClass(),
            'showExportButton' => false,
            'externalFilters' => new \stdClass(),
        ];

        return response()->json([
            'status' => true,
            'data' => [
                'rooms' => array_merge($emptyExtras, [
                    'headers' => $roomsHeaders,
                    'exportColumns' => $roomsHeaders['fields'],
                ]),
                'beds' => array_merge($emptyExtras, [
                    'headers' => $bedsHeaders,
                    'exportColumns' => $bedsHeaders['fields'],
                ]),
            ],
        ]);
    }

    public function index(AvailabilityData $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $raw = $this->availability->availability(
            $request->check_in_on,
            $request->check_out_on,
            $request->building_id
        );

        $roomTypeTitles = RoomType::on(config('tenancy.connection_name'))
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => (string) $name])
            ->all();

        $dormitoryTitles = HutDormitory::on(config('tenancy.connection_name'))
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => (string) $name])
            ->all();

        $rooms = array_map(function (array $room) use ($roomTypeTitles) {
            $available = (bool) ($room['available'] ?? false);
            $typeId = (string) ($room['room_type_id'] ?? '');

            return [
                'id' => (int) $room['id'],
                'uuid' => (string) $room['id'],
                'room_number' => ['label' => (string) ($room['room_number'] ?? '--'), 'type' => 'text'],
                'name' => ['label' => ($room['name'] ?? '') !== '' && $room['name'] !== null ? (string) $room['name'] : '--', 'type' => 'text'],
                'room_type' => [
                    'label' => $roomTypeTitles[$typeId] ?? ('Type #'.$typeId),
                    'type' => 'text',
                ],
                'accommodation_type' => [
                    'label' => (string) ($room['accommodation_type'] ?? '--'),
                    'type' => 'text',
                ],
                'availability' => [
                    'label' => $available ? 'Free' : 'Taken',
                    'type' => 'status',
                    'color' => $available ? 'success' : 'error',
                ],
                'actions' => [
                    'writePermission' => false,
                    'viewPermission' => false,
                    'selectable' => false,
                    'isLocked' => false,
                ],
            ];
        }, $raw['rooms']);

        $beds = array_map(function (array $bed) use ($dormitoryTitles) {
            $available = (bool) ($bed['available'] ?? false);
            $dormId = (string) ($bed['hut_dormitory_id'] ?? '');

            return [
                'id' => (int) $bed['id'],
                'uuid' => (string) $bed['id'],
                'dormitory' => [
                    'label' => $dormitoryTitles[$dormId] ?? ('#'.$dormId),
                    'type' => 'text',
                ],
                'bed_number' => ['label' => (string) ($bed['bed_number'] ?? '--'), 'type' => 'text'],
                'availability' => [
                    'label' => $available ? 'Free' : 'Taken',
                    'type' => 'status',
                    'color' => $available ? 'success' : 'error',
                ],
                'actions' => [
                    'writePermission' => false,
                    'viewPermission' => false,
                    'selectable' => false,
                    'isLocked' => false,
                ],
            ];
        }, $raw['beds']);

        return response()->json([
            'success' => true,
            'data' => [
                'rooms' => $rooms,
                'beds' => $beds,
                'free_room_count' => count(array_filter($raw['rooms'], fn ($r) => (bool) ($r['available'] ?? false))),
                'free_bed_count' => count(array_filter($raw['beds'], fn ($b) => (bool) ($b['available'] ?? false))),
                'room_count' => count($raw['rooms']),
                'bed_count' => count($raw['beds']),
            ],
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
