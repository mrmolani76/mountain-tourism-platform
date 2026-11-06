<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hotel\App\Support\HotelAuthz;

/**
 * EQMS-style DataTable listing for Hotel resources.
 *
 *   GET  /api/hotel/listings/{resource}/config
 *   POST /api/hotel/listings/{resource}/list
 */
class HotelListingController extends Controller
{
    public function config(string $resource): JsonResponse
    {
        if ($denied = $this->denyFor($resource)) {
            return $denied;
        }

        $cfg = $this->resourceConfig($resource);
        if ($cfg === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'headers' => $cfg['headers'],
                'bulkActions' => [],
                'columnSearchable' => $this->columnSearchable($resource),
                'showCheckbox' => false,
                'selectedColumns' => [],
                'showAddButton' => ! in_array($resource, ['reservations', 'stays'], true),
                'advanceFilters' => new \stdClass(),
                'showExportButton' => false,
                'exportColumns' => $cfg['headers']['fields'],
                'externalFilters' => new \stdClass(),
            ],
        ]);
    }

    public function list(Request $request, string $resource): JsonResponse
    {
        if ($denied = $this->denyFor($resource)) {
            return $denied;
        }

        $cfg = $this->resourceConfig($resource);
        if ($cfg === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $cfg['model'];
        $q = $modelClass::on(config('tenancy.connection_name'));

        $this->applySearch($q, $request, $cfg);
        $this->applySort($q, $request, $cfg);

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

        $rows = $items->map(fn ($model) => $this->listRow($model, $cfg))->values()->all();

        return response()->json([
            'status' => true,
            'data' => $rows,
            'pagination' => ['total' => $total],
        ]);
    }

    private function denyFor(string $resource): ?JsonResponse
    {
        // Listings are read surfaces (Front Desk + Owner), same as resource index.
        return HotelAuthz::denyUnlessFrontDesk();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resourceConfig(string $resource): ?array
    {
        $all = config('hotel_listing', []);

        return is_array($all[$resource] ?? null) ? $all[$resource] : null;
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
     * @param  array<string, mixed>  $cfg
     */
    private function applySearch(Builder $q, Request $request, array $cfg): void
    {
        $columnSearch = $request->input('columnSearch', []);
        if (! is_array($columnSearch)) {
            return;
        }

        foreach ($cfg['search_columns'] ?? [] as $col) {
            if (! empty($columnSearch[$col])) {
                $q->where($col, 'ilike', '%'.$columnSearch[$col].'%');
            }
        }

        if (array_key_exists('status', $columnSearch) && $columnSearch['status'] !== '' && $columnSearch['status'] !== null) {
            if (in_array(($cfg['table'] ?? ''), [
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
     * @param  array<string, mixed>  $cfg
     */
    private function applySort(Builder $q, Request $request, array $cfg): void
    {
        $sortBy = $request->input('sortBy');
        $key = $cfg['headers']['default_sort']['key'] ?? 'id';
        $order = $cfg['headers']['default_sort']['order'] ?? 'asc';

        if (is_array($sortBy)) {
            if (isset($sortBy['key'])) {
                $key = (string) $sortBy['key'];
                $order = (string) ($sortBy['order'] ?? 'asc');
            } elseif (isset($sortBy[0]) && is_array($sortBy[0])) {
                $key = (string) ($sortBy[0]['key'] ?? $key);
                $order = (string) ($sortBy[0]['order'] ?? $order);
            }
        }

        if ($key === 'status' && isset($cfg['table']) && str_contains((string) $cfg['table'], 'hotel_')) {
            $key = in_array($cfg['table'], [
                'hotel_reservation', 'hotel_stay',
            ], true) ? 'status' : 'is_active';
        }

        $allowed = collect($cfg['headers']['fields'] ?? [])->pluck('key')->all();
        if (! in_array($key, $allowed, true) && $key !== 'is_active') {
            $key = 'id';
        }

        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';
        $q->orderBy($key, $order);
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<string, mixed>
     */
    private function listRow(object $model, array $cfg): array
    {
        $id = (int) $model->getKey();
        $viewPath = rtrim((string) ($cfg['view_path'] ?? ''), '/');
        $linkKey = (string) ($cfg['link_key'] ?? 'name');
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

        foreach ($cfg['headers']['fields'] as $field) {
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
