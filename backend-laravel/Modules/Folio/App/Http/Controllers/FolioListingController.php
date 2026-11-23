<?php

namespace Modules\Folio\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Services\ListingConfigService;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Services\FolioService;
use Modules\Folio\App\Support\FolioAuthz;
use Modules\Hotel\App\Models\Stay;

/**
 * EQMS DataTable listing for guest folios.
 *
 *   GET  /api/folios/list/config
 *   POST /api/folios/list
 *
 * Money APIs (charge/pay/void) stay on FolioController — append-only.
 */
class FolioListingController extends Controller
{
    public function __construct(
        private readonly FolioService $folios,
    ) {
    }

    public function listingConfig(): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $table = ListingConfigService::table('folios', 'folio_listing');
        if ($table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        return response()->json([
            'status' => true,
            'data' => ListingConfigService::build(
                $table,
                [
                    'status' => [
                        ['value' => Folio::STATUS_OPEN, 'title' => 'Open'],
                        ['value' => Folio::STATUS_CLOSED, 'title' => 'Closed'],
                    ],
                ],
                bulkActions: [],
                showAddButton: false,
            ),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $table = ListingConfigService::table('folios', 'folio_listing');
        if ($table === null) {
            return Envelope::errorJson('not_found', 'Unknown listing resource.', [], 404);
        }

        $q = Folio::on(config('tenancy.connection_name'));
        $this->applySearch($q, $request);
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

        $rows = $items->map(fn (Folio $folio) => $this->listRow($folio))->values()->all();

        return response()->json([
            'status' => true,
            'data' => $rows,
            'pagination' => ['total' => $total],
        ]);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     */
    private function applySearch(Builder $q, Request $request): void
    {
        $columnSearch = $request->input('columnSearch', []);
        if (! is_array($columnSearch)) {
            return;
        }

        if (! empty($columnSearch['reference'])) {
            $q->where('reference', 'like', '%'.$columnSearch['reference'].'%');
        }

        if (! empty($columnSearch['folio_id'])) {
            $q->where('id', (int) $columnSearch['folio_id']);
        }
        if (! empty($columnSearch['id'])) {
            $q->where('id', (int) $columnSearch['id']);
        }

        if (! empty($columnSearch['status'])) {
            $statuses = is_array($columnSearch['status'])
                ? $columnSearch['status']
                : [$columnSearch['status']];
            $q->whereIn('status', $statuses);
        }

        if (isset($columnSearch['stay_id']) && $columnSearch['stay_id'] !== '' && $columnSearch['stay_id'] !== null) {
            $stayId = (int) $columnSearch['stay_id'];
            $folioIds = Stay::on(config('tenancy.connection_name'))
                ->where('id', $stayId)
                ->pluck('folio_id')
                ->filter()
                ->all();
            $q->whereIn('id', $folioIds ?: [0]);
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $q
     * @param  array<string, mixed>  $table
     */
    private function applySort(Builder $q, Request $request, array $table): void
    {
        $sortBy = $request->input('sortBy');
        $key = $table['default_sort']['key'] ?? 'id';
        $order = $table['default_sort']['order'] ?? 'desc';

        if (is_array($sortBy)) {
            if (isset($sortBy['key'])) {
                $key = (string) $sortBy['key'];
                $order = (string) ($sortBy['order'] ?? 'asc');
            } elseif (isset($sortBy[0]) && is_array($sortBy[0])) {
                $key = (string) ($sortBy[0]['key'] ?? $key);
                $order = (string) ($sortBy[0]['order'] ?? $order);
            }
        }

        $allowed = ['id', 'reference', 'status', 'opened_at', 'closed_at'];
        if (! in_array($key, $allowed, true)) {
            $key = 'id';
        }

        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        $q->orderBy($key, $order);
    }

    /**
     * @return array<string, mixed>
     */
    private function listRow(Folio $folio): array
    {
        $id = (int) $folio->getKey();
        $api = $folio->toApiArray();
        $balance = $this->folios->balanceCents($folio);
        $stay = Stay::on(config('tenancy.connection_name'))
            ->where('folio_id', $id)
            ->orderByDesc('id')
            ->first();
        $stayId = $stay === null ? null : (int) $stay->getKey();

        $status = (string) ($api['status'] ?? '');
        $ref = (string) ($api['reference'] ?? '');

        return [
            'uuid' => (string) $id,
            'id' => $id,
            'actions' => [
                'writePermission' => false,
                'viewPermission' => true,
                'selectable' => false,
                'isLocked' => false,
            ],
            'folio_id' => [
                'label' => (string) $id,
                'type' => 'link',
                'url' => '/folios/view/'.$id,
            ],
            'reference' => [
                'label' => $ref !== '' ? $ref : '—',
                'type' => 'link',
                'url' => '/folios/view/'.$id,
            ],
            'status' => [
                'label' => $status !== '' ? $status : '—',
                'type' => 'status',
                'color' => $status === Folio::STATUS_OPEN ? 'success' : 'secondary',
            ],
            'stay_id' => $stayId === null
                ? ['label' => '—', 'type' => 'text']
                : [
                    'label' => 'Stay '.$stayId,
                    'type' => 'link',
                    'url' => '/hotel/stays/view/'.$stayId,
                ],
            'balance_cents' => [
                'label' => number_format($balance / 100, 2),
                'type' => 'text',
            ],
            'opened_at' => [
                'label' => $api['opened_at'] ? substr((string) $api['opened_at'], 0, 16) : '—',
                'type' => 'text',
            ],
        ];
    }
}
