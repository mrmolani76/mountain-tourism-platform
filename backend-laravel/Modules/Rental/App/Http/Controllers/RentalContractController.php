<?php

namespace Modules\Rental\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Rental\App\Http\Requests\RentOutRequest;
use Modules\Rental\App\Http\Requests\ReturnItemRequest;
use Modules\Rental\App\Models\Rental;
use Modules\Rental\App\Models\RentalItem;
use Modules\Rental\App\Services\RentalService;
use Modules\Rental\App\Support\RentalAuthz;

class RentalContractController extends Controller
{
    public function __construct(private readonly RentalService $rentals)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $query = Rental::on(config('tenancy.connection_name'))->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $rows = $query->limit(50)->get()->map(fn (Rental $r) => $r->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(RentOutRequest $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $rental = $this->rentals->rentOut($request->validated());
        $payload = $rental->toApiArray();
        $payload['items'] = RentalItem::on(config('tenancy.connection_name'))
            ->where('rental_id', $rental->getKey())
            ->get()
            ->map(fn (RentalItem $i) => $i->toApiArray())
            ->all();

        return response()->json($payload, 201);
    }

    public function show(int $rental): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $model = Rental::on(config('tenancy.connection_name'))->find($rental);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json([
            'rental' => $model->toApiArray(),
            'items' => RentalItem::on(config('tenancy.connection_name'))
                ->where('rental_id', $model->getKey())
                ->get()
                ->map(fn (RentalItem $i) => $i->toApiArray())
                ->all(),
        ], 200);
    }

    public function returnItem(ReturnItemRequest $request, int $rental_item): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $line = RentalItem::on(config('tenancy.connection_name'))->find($rental_item);
        if ($line === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $returned = $this->rentals->returnItem($line, $request->validated());

        return response()->json($returned->toApiArray(), 200);
    }

    public function currentlyOut(Request $request): JsonResponse
    {
        if ($denied = RentalAuthz::denyUnlessRentalDesk()) {
            return $denied;
        }

        $overdueOnly = $request->boolean('overdue');
        $rows = $this->rentals->currentlyOut($overdueOnly);

        return response()->json(['results' => $rows], 200);
    }
}
