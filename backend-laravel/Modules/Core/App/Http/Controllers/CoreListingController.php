<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Season;
use Modules\Core\App\Support\StaffAuthz;

/**
 * EQMS DataTable listing for Core resources.
 *
 *   GET  /api/{resource}/list/config
 *   POST /api/{resource}/list
 */
class CoreListingController extends Controller
{
    public function listingConfig(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $resource = $this->resolveResource($request);
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
                'showAddButton' => true,
                'advanceFilters' => new \stdClass(),
                'showExportButton' => false,
                'exportColumns' => $cfg['headers']['fields'],
                'externalFilters' => new \stdClass(),
            ],
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessOwner()) {
            return $denied;
        }

        $resource = $this->resolveResource($request);
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

    private function resolveResource(Request $request): string
    {
        $fromDefaults = (string) $request->route('resource', '');
        if ($fromDefaults !== '') {
            return $fromDefaults;
        }

        $path = trim($request->path(), '/');
        if (preg_match('#^([^/]+)/list#', $path, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resourceConfig(string $resource): ?array
    {
        $all = config('listing', []);

        return is_array($all[$resource] ?? null) && isset($all[$resource]['model'])
            ? $all[$resource]
            : null;
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

        if (array_key_exists('status', $columnSearch) && $columnSearch['status'] !== '' && $columnSearch['status'] !== null) {
            $q->where('is_active', (bool) (int) $columnSearch['status']);
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

        if ($key === 'status') {
            $key = 'is_active';
        }

        $allowed = collect($cfg['headers']['fields'] ?? [])->pluck('key')->all();
        $allowed[] = 'is_active';
        if (! in_array($key, $allowed, true)) {
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
        if ($model instanceof Building) {
            $api = [
                'id' => $id,
                'name' => $model->getAttribute('name'),
                'building_type' => $model->getAttribute('building_type'),
                'altitude_m' => $model->getAttribute('altitude_m'),
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

        $linkKey = (string) ($cfg['link_key'] ?? 'name');
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

        foreach ($cfg['headers']['fields'] as $field) {
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
