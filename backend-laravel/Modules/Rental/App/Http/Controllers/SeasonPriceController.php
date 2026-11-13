<?php

namespace Modules\Rental\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Rental\App\Http\Requests\StoreSeasonPriceRequest;
use Modules\Rental\App\Models\SeasonPrice;
use Modules\Rental\App\Support\RentalAuthz;

class SeasonPriceController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $rows = SeasonPrice::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (SeasonPrice $p) => $p->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreSeasonPriceRequest $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $row = SeasonPrice::on(config('tenancy.connection_name'))->updateOrCreate(
            [
                'equipment_type_id' => (int) $request->validated('equipment_type_id'),
                'season_id' => (int) $request->validated('season_id'),
            ],
            ['day_rate_cents' => (int) $request->validated('day_rate_cents')]
        );

        return response()->json($row->toApiArray(), 201);
    }
}
