<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Services\ListingConfigService;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\RoomSeasonPrice;
use Modules\Hotel\App\Models\RoomType;
use Modules\Hotel\App\Models\Stay;
use Modules\Hotel\App\Support\HotelAuthz;

/**
 * EQMS-style DataTable listing for Hotel resources.
 *
 *   GET  /api/hotel/{resource}/list/config
 *   POST /api/hotel/{resource}/list
 *
 * Columns: Modules/Hotel/config/listing.php (flat fields).
 * Model meta: RESOURCES below.
 */
class HotelListingController extends Controller
{
    /**
     * @var array<string, array{model: class-string<\Illuminate\Database\Eloquent\Model>, table: string, view_path: string, link_key: string, search_columns: list<string>}>
     */
    private const RESOURCES = [
        'room-types' => [
            'model' => RoomType::class,
            'table' => 'hotel_room_type',
            'view_path' => '/hotel/room-types',
            'link_key' => 'name',
            'search_columns' => ['name'],
        ],
        'rooms' => [
            'model' => Room::class,
            'table' => 'hotel_room',
            'view_path' => '/hotel/rooms',
            'link_key' => 'room_number',
            'search_columns' => ['room_number', 'name'],
        ],
        'hut-dormitories' => [
            'model' => HutDormitory::class,
            'table' => 'hotel_hut_dormitory',
            'view_path' => '/hotel/hut-dormitories',
            'link_key' => 'name',
            'search_columns' => ['name'],
        ],
        'hut-beds' => [
            'model' => HutBed::class,
            'table' => 'hotel_hut_bed',
            'view_path' => '/hotel/hut-beds',
            'link_key' => 'bed_number',
            'search_columns' => ['bed_number'],
        ],
        'season-prices' => [
            'model' => RoomSeasonPrice::class,
            'table' => 'hotel_room_season_price',
            'view_path' => '/hotel/season-prices',
            'link_key' => 'id',
            'search_columns' => [],
        ],
        'board-plans' => [
            'model' => BoardPlan::class,
            'table' => 'hotel_board_plan',
            'view_path' => '/hotel/board-plans',
            'link_key' => 'name',
            'search_columns' => ['name', 'code'],
        ],
        'reservations' => [
            'model' => Reservation::class,
            'table' => 'hotel_reservation',
            'view_path' => '/hotel/reservations',
            'link_key' => 'reference',
            'search_columns' => ['reference', 'guest_name'],
        ],
        'stays' => [
            'model' => Stay::class,
            'table' => 'hotel_stay',
            'view_path' => '/hotel/stays',
            'link_key' => 'id',
            'search_columns' => [],
        ],
    ];

    public function listingConfig(Request $request): JsonResponse
    {
        $resource = $this->resolveResource($request);

        if ($denied = $this->denyFor($resource)) {
            return $denied;
        }

        if (! isset(self::RESOURCES[$resource])) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        $table = ListingConfigService::table($resource, 'hotel_listing');
        if ($table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => ListingConfigService::build(
                $table,
                $this->columnSearchable($resource),
                bulkActions: [],
                showAddButton: ! in_array($resource, ['reservations', 'stays'], true),
            ),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $resource = $this->resolveResource($request);

        if ($denied = $this->denyFor($resource)) {
            return $denied;
        }

        $meta = self::RESOURCES[$resource] ?? null;
        $table = ListingConfigService::table($resource, 'hotel_listing');
        if ($meta === null || $table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $meta['model'];
        $q = $modelClass::on(config('tenancy.connection_name'));

        $this->applySearch($q, $request, $meta);
        $this->applySort($q, $request, $table, $meta['table']);

        $page = max(1, (int) $request->input('page', 1));
        $size = (int) $request->input('itemsPerPage', $request->input('page_size', 10));
        if ($size === -1) {
            $size = 100;
        }
        if ($size < 1) {
            $size = 10;
        }
        if ($size > 100) {
            $size = 100;
        }

        $total = (clone $q)->count();
        $items = (clone $q)->skip(($page - 1) * $size)->take($size)->get();

        $rows = $items->map(fn ($model) => $this->listRow($model, $table, $meta))->values()->all();

        return response()->json([
            'status' => true,
            'data' => $rows,
            'pagination' => ['total' => $total],
        ]);
    }

    private function resolveResource(Request $request): string
    {
        $fromDefaults = (string) $request->route('resource', '');
        if ($fromDefaults !== '') {
            return $fromDefaults;
        }

        $path = trim($request->path(), '/');
        if (preg_match('#hotel/([^/]+)/list#', $path, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    private function denyFor(string $resource): ?JsonResponse
    {
        return HotelAuthz::denyUnlessFrontDesk();
    }

    /**
     * @return array<string, array<int, array{value: mixed, title: string}>>
     */
    private function columnSearchable(string $resource): array
    {
        if (in_array($resource, ['room-types', 'rooms', 'hut-dormitories', 'hut-beds', 'board-plans'], true)) {
            return [
                'status' => [
                    ['value' => 1, 'title' => 'Active'],
                    ['value' => 0, 'title' => 'Inactive'],
                ],
            ];
        }

        if ($resource === 'reservations') {
            return [
                'status' => [
                    ['value' => 'pending', 'title' => 'Pending'],
                    ['value' => 'confirmed', 'title' => 'Confirmed'],
                    ['value' => 'checked_in', 'title' => 'Checked in'],
                    ['value' => 'checked_out', 'title' => 'Checked out'],
                    ['value' => 'cancelled', 'title' => 'Cancelled'],
                ],
            ];
        }

        if ($resource === 'stays') {
            return [
                'status' => [
                    ['value' => 'in_house', 'title' => 'In house'],
                    ['value' => 'checked_out', 'title' => 'Checked out'],
                ],
            ];
        }

        return [];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array{table: string, search_columns: list<string>}  $meta
     */
    private function applySearch(Builder $q, Request $request, array $meta): void
    {
        $columnSearch = $request->input('columnSearch', []);
        if (! is_array($columnSearch)) {
            return;
        }

        foreach ($meta['search_columns'] as $col) {
            if (! empty($columnSearch[$col])) {
                $q->where($col, 'like', '%'.$columnSearch[$col].'%');
            }
        }

        if (array_key_exists('status', $columnSearch) && $columnSearch['status'] !== '' && $columnSearch['status'] !== null) {
            if (in_array($meta['table'], [
                'hotel_room_type', 'hotel_room', 'hotel_hut_dormitory', 'hotel_hut_bed', 'hotel_board_plan',
            ], true)) {
                $q->where('is_active', (bool) (int) $columnSearch['status']);
            } else {
                $statuses = is_array($columnSearch['status']) ? $columnSearch['status'] : [$columnSearch['status']];
                $q->whereIn('status', $statuses);
            }
        }

        foreach (['building_id', 'room_type_id', 'hut_dormitory_id', 'season_id', 'reservation_id'] as $fk) {
            if (isset($columnSearch[$fk]) && $columnSearch[$fk] !== '' && $columnSearch[$fk] !== null) {
                $q->where($fk, (int) $columnSearch[$fk]);
            }
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $table
     */
    private function applySort(Builder $q, Request $request, array $table, string $tableName): void
    {
        $sortBy = $request->input('sortBy');
        $key = $table['default_sort']['key'] ?? 'id';
        $order = $table['default_sort']['order'] ?? 'asc';

        if (is_array($sortBy)) {
            if (isset($sortBy['key'])) {
                $key = (string) $sortBy['key'];
                $order = (string) ($sortBy['order'] ?? 'asc');
            } elseif (isset($sortBy[0]) && is_array($sortBy[0])) {
                $key = (string) ($sortBy[0]['key'] ?? $key);
                $order = (string) ($sortBy[0]['order'] ?? $order);
            }
        }

        if ($key === 'status') {
            $key = in_array($tableName, ['hotel_reservation', 'hotel_stay'], true) ? 'status' : 'is_active';
        }

        $allowed = collect($table['fields'] ?? [])->pluck('key')->all();
        if (! in_array($key, $allowed, true) && $key !== 'is_active') {
            $key = 'id';
        }

        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';
        $q->orderBy($key, $order);
    }

    /**
     * @param  array<string, mixed>  $table
     * @param  array{view_path: string, link_key: string}  $meta
     * @return array<string, mixed>
     */
    private function listRow(object $model, array $table, array $meta): array
    {
        $id = (int) $model->getKey();
        $viewPath = rtrim($meta['view_path'], '/');
        $linkKey = $meta['link_key'];
        $api = method_exists($model, 'toApiArray') ? $model->toApiArray() : $model->toArray();

        $row = [
            'uuid' => (string) $id,
            'id' => $id,
            'actions' => [
                'writePermission' => true,
                'viewPermission' => true,
                'selectable' => false,
                'isLocked' => false,
            ],
        ];

        foreach ($table['fields'] as $field) {
            $key = $field['key'];
            if ($key === 'actions') {
                continue;
            }

            if ($key === 'status') {
                if (array_key_exists('is_active', $api)) {
                    $active = (bool) $api['is_active'];
                    $row['status'] = [
                        'label' => $active ? 'Active' : 'Inactive',
                        'type' => 'status',
                        'color' => $active ? 'success' : 'error',
                    ];
                } else {
                    $status = (string) ($api['status'] ?? '');
                    $row['status'] = [
                        'label' => $status !== '' ? $status : '--',
                        'type' => 'status',
                        'color' => $status === 'cancelled' ? 'error' : ($status === 'checked_in' || $status === 'in_house' ? 'success' : 'primary'),
                    ];
                }

                continue;
            }

            if ($key === 'amount_cents' || $key === 'price_per_person_cents') {
                $cents = (int) ($api[$key] ?? 0);
                $row[$key] = [
                    'label' => number_format($cents / 100, 2),
                    'type' => 'text',
                ];

                continue;
            }

            $value = $api[$key] ?? null;
            $label = $value === null || $value === '' ? '--' : (is_array($value) ? implode(', ', $value) : (string) $value);

            if ($key === $linkKey) {
                $row[$key] = [
                    'label' => $label,
                    'type' => 'link',
                    'url' => $viewPath.'/view/'.$id,
                ];
            } else {
                $row[$key] = [
                    'label' => $label,
                    'type' => 'text',
                ];
            }
        }

        return $row;
    }
}
