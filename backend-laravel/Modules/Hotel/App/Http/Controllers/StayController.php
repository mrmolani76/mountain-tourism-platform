<?php

namespace Modules\Hotel\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Stay;
use Modules\Hotel\App\Services\CheckoutService;
use Modules\Hotel\App\Support\HotelAuthz;
use Modules\Hotel\App\Support\ListQuery;

class StayController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly FolioService $folios,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        [$page, $size] = ListQuery::page($request);

        $query = Stay::on(config('tenancy.connection_name'))
            ->with('reservation.allocations')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->orderByDesc('checked_in_at');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        return ListQuery::respond($items, $total, $request, fn (Stay $stay) => $stay->toApiArray());
    }

    public function show(int $stay): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Stay::on(config('tenancy.connection_name'))
            ->with('reservation.allocations')
            ->find($stay);

        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $folio = $model->getAttribute('folio_id') === null
            ? null
            : Folio::on(config('tenancy.connection_name'))->find((int) $model->getAttribute('folio_id'));

        return response()->json([
            'stay' => $model->toApiArray(),
            'reservation' => $model->reservation?->toApiArray(),
            'folio' => $folio?->toApiArray(),
            'balance_cents' => $folio === null ? null : $this->folios->balanceCents($folio),
        ], 200);
    }

    public function checkOut(int $stay): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFrontDesk()) {
            return $denied;
        }

        $model = Stay::on(config('tenancy.connection_name'))->find($stay);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $closed = $this->checkout->checkOut($model);

        return response()->json($closed->toApiArray(), 200);
    }

    /** Open folio snapshot for the front desk (charges, payments, balance). */
    public function folio(int $stay): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $model = Stay::on(config('tenancy.connection_name'))->find($stay);
        if ($model === null || $model->getAttribute('folio_id') === null) {
            return Envelope::errorJson('not_found', 'Stay has no folio.', [], 404);
        }

        $folio = Folio::on(config('tenancy.connection_name'))->find((int) $model->getAttribute('folio_id'));
        if ($folio === null) {
            return Envelope::errorJson('not_found', 'Folio not found.', [], 404);
        }

        return response()->json([
            'folio' => $folio->toApiArray(),
            'charges' => Charge::on(config('tenancy.connection_name'))
                ->where('folio_id', $folio->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (Charge $charge) => $charge->toApiArray())
                ->all(),
            'payments' => Payment::on(config('tenancy.connection_name'))
                ->where('folio_id', $folio->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (Payment $payment) => $payment->toApiArray())
                ->all(),
            'balance_cents' => $this->folios->balanceCents($folio),
        ], 200);
    }
}
