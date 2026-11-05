<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hotel\App\Http\Requests\RunNightlyPostingRequest;
use Modules\Hotel\App\Models\NightlyPosting;
use Modules\Hotel\App\Services\NightlyPostingService;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class NightlyPostingController extends Controller
{
    public function __construct(private readonly NightlyPostingService $posting)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = NightlyPosting::on(config('tenancy.connection_name'))
            ->when($request->filled('date'), fn ($q) => $q->where('posted_on', $request->query('date')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('stay_id'), fn ($q) => $q->where('stay_id', (int) $request->query('stay_id')))
            ->orderByDesc('posted_on')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (NightlyPosting $posting) => $posting->toApiArray());
    }

    public function run(RunNightlyPostingRequest $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $date = $request->validated()['date'] ?? null;

        return response()->json([
            'success' => true,
            'data' => $this->posting->run($date),
            'error' => null,
            'meta' => [],
        ], 200);
    }
}
