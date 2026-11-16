<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\Season;
use Modules\Core\App\Services\ListingConfigService;
use Modules\Core\App\Support\StaffAuthz;

/**
 * EQMS DataTable listing for Core resources (buildings / seasons / outlets).
 *
 * Pattern (eQMS Complere):
 *   GET  /api/{resource}/list/config  → ListingConfigService envelope
 *   POST /api/{resource}/list         → paged rows
 *
 * Listing columns: Modules/Core/config/listing.php (flat fields + default_order).
 * Model meta: RESOURCES map below (EQMS keeps model in the controller, not listing.php).
 */
class CoreListingController extends Controller
{
    /**
     * @var array<string, array{model: class-string<\Illuminate\Database\Eloquent\Model>, link_key: string, search_columns: list<string>}>
     */
    private const RESOURCES = [
        'buildings' => [
            'model' => Building::class,
            'link_key' => 'name',
            'search_columns' => ['name'],
        ],
        'seasons' => [
            'model' => Season::class,
            'link_key' => 'kind',
            'search_columns' => [],
        ],
        'outlets' => [
            'model' => Outlet::class,
            'link_key' => 'name',
            'search_columns' => ['name'],
        ],
    ];

    public function listingConfig(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $resource = $this->resolveResource($request);
        if (! isset(self::RESOURCES[$resource])) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        $table = ListingConfigService::table($resource);
        if ($table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => ListingConfigService::build(
                $table,
                $this->columnSearchable($resource),
                bulkActions: [],
                showAddButton: true,
            ),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $resource = $this->resolveResource($request);
        $meta = self::RESOURCES[$resource] ?? null;
        $table = ListingConfigService::table($resource);
        if ($meta === null || $table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $meta['model'];
        $q = $modelClass::on(config('tenancy.connection_name'));

        $this->applySearch($q, $request, $meta['search_columns']);
        $this->applySort($q, $request, $table);

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

        $rows = $items->map(
            fn ($model) => $this->listRow($model, $table, $meta['link_key'])
        )->values()->all();

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
        if (preg_match('#^api/([^/]+)/list#', $path, $matches) === 1
            || preg_match('#^([^/]+)/list#', $path, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @return array<string, array<int, array{value: mixed, title: string}>>
     */
    private function columnSearchable(string $resource): array
    {
        if ($resource === 'buildings') {
            $types = [];
            foreach (Building::TYPES as $code => $label) {
                $types[] = ['value' => $code, 'title' => $label];
            }

            return [
                'building_type' => $types,
                'status' => [
                    ['value' => 1, 'title' => 'Active'],
                    ['value' => 0, 'title' => 'Inactive'],
                ],
            ];
        }

        if ($resource === 'seasons') {
            return [
                'kind' => [
                    ['value' => 'winter', 'title' => 'Winter'],
                    ['value' => 'summer', 'title' => 'Summer'],
                    ['value' => 'closed', 'title' => 'Closed'],
                ],
                'status' => [
                    ['value' => 1, 'title' => 'Active'],
                    ['value' => 0, 'title' => 'Inactive'],
                ],
            ];
        }

        if ($resource === 'outlets') {
            $types = [];
            foreach (Outlet::TYPES as $code => $label) {
                $types[] = ['value' => $code, 'title' => $label];
            }

            return [
                'outlet_type' => $types,
                'status' => [
                    ['value' => 1, 'title' => 'Active'],
                    ['value' => 0, 'title' => 'Inactive'],
                ],
            ];
        }

        return [];
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  list<string>  $searchColumns
     */
    private function applySearch(Builder $q, Request $request, array $searchColumns): void
    {
        $columnSearch = $request->input('columnSearch', []);
        if (! is_array($columnSearch)) {
            return;
        }

        foreach ($searchColumns as $col) {
            if (! empty($columnSearch[$col])) {
                $q->where($col, 'like', '%'.$columnSearch[$col].'%');
            }
        }

        if (! empty($columnSearch['building_type'])) {
            $types = is_array($columnSearch['building_type'])
                ? $columnSearch['building_type']
                : [$columnSearch['building_type']];
            $q->whereIn('building_type', $types);
        }

        if (! empty($columnSearch['kind'])) {
            $kinds = is_array($columnSearch['kind']) ? $columnSearch['kind'] : [$columnSearch['kind']];
            $q->whereIn('kind', $kinds);
        }

        if (! empty($columnSearch['outlet_type'])) {
            $types = is_array($columnSearch['outlet_type'])
                ? $columnSearch['outlet_type']
                : [$columnSearch['outlet_type']];
            $q->whereIn('outlet_type', $types);
        }

        if (array_key_exists('status', $columnSearch) && $columnSearch['status'] !== '' && $columnSearch['status'] !== null) {
            $q->where('is_active', (bool) (int) $columnSearch['status']);
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $table
     */
    private function applySort(Builder $q, Request $request, array $table): void
    {
        $sortBy = $request->input('sortBy');
        $key = $table['default_sort']['key'] ?? $table['default_order']['field'] ?? 'id';
        $order = $table['default_sort']['order'] ?? $table['default_order']['dir'] ?? 'asc';

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
            $key = 'is_active';
        }

        $allowed = collect($table['fields'] ?? [])->pluck('key')->all();
        $allowed[] = 'is_active';
        if (! in_array($key, $allowed, true)) {
            $key = 'id';
        }

        $order = strtolower((string) $order) === 'desc' ? 'desc' : 'asc';
        $q->orderBy($key, $order);
    }

    /**
     * @param  array<string, mixed>  $table
     * @return array<string, mixed>
     */
    private function listRow(object $model, array $table, string $linkKey): array
    {
        $id = (int) $model->getKey();
        if ($model instanceof Building) {
            $api = [
                'id' => $id,
                'name' => $model->getAttribute('name'),
                'building_type' => $model->getAttribute('building_type'),
                'altitude_m' => $model->getAttribute('altitude_m'),
                'is_active' => (bool) $model->getAttribute('is_active'),
            ];
        } elseif ($model instanceof Outlet) {
            $api = [
                'id' => $id,
                'name' => $model->getAttribute('name'),
                'outlet_type' => $model->getAttribute('outlet_type'),
                'building_id' => $model->getAttribute('building_id'),
                'is_active' => (bool) $model->getAttribute('is_active'),
            ];
        } else {
            $starts = $model->getAttribute('starts_on');
            $ends = $model->getAttribute('ends_on');
            $api = [
                'id' => $id,
                'kind' => $model->getAttribute('kind'),
                'starts_on' => $starts instanceof \DateTimeInterface ? $starts->format('Y-m-d') : $starts,
                'ends_on' => $ends instanceof \DateTimeInterface ? $ends->format('Y-m-d') : $ends,
                'is_active' => (bool) $model->getAttribute('is_active'),
            ];
        }

        $row = [
            'uuid' => (string) $id,
            'id' => $id,
            'actions' => [
                'writePermission' => true,
                'viewPermission' => false,
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
                $active = (bool) ($api['is_active'] ?? false);
                $row['status'] = [
                    'label' => $active ? 'Active' : 'Inactive',
                    'type' => 'status',
                    'color' => $active ? 'success' : 'error',
                ];

                continue;
            }

            if ($key === 'building_type') {
                $code = (string) ($api['building_type'] ?? '');
                $label = Building::TYPES[$code] ?? ($code !== '' ? $code : '--');
                $row[$key] = ['label' => $label, 'type' => 'text'];

                continue;
            }

            if ($key === 'outlet_type') {
                $code = (string) ($api['outlet_type'] ?? '');
                $label = Outlet::TYPES[$code] ?? ($code !== '' ? $code : '--');
                $row[$key] = ['label' => $label, 'type' => 'text'];

                continue;
            }

            $value = $api[$key] ?? null;
            if ($key === 'starts_on' || $key === 'ends_on') {
                $label = $value ? substr((string) $value, 0, 10) : '--';
            } else {
                $label = $value === null || $value === '' ? '--' : (string) $value;
            }

            $row[$key] = [
                'label' => $label,
                'type' => $key === $linkKey ? 'text' : 'text',
            ];
        }

        return $row;
    }
}
