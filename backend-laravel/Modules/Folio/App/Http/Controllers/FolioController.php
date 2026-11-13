<?php

namespace Modules\Folio\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Folio\App\Http\Requests\ChargeToRoomRequest;
use Modules\Folio\App\Http\Requests\StoreChargeRequest;
use Modules\Folio\App\Http\Requests\StorePaymentRequest;
use Modules\Folio\App\Http\Requests\VoidRequest;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\ChargeToRoomService;
use Modules\Folio\App\Services\FolioService;
use Modules\Folio\App\Services\InvoiceService;
use Modules\Folio\App\Support\FolioAuthz;
use Modules\Hotel\App\Models\Stay;

class FolioController extends Controller
{
    public function __construct(
        private readonly FolioService $folios,
        private readonly ChargeToRoomService $chargeToRoom,
        private readonly InvoiceService $invoices,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $status = $request->query('status');
        $query = Folio::on(config('tenancy.connection_name'))->orderByDesc('id');

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%'.$request->query('reference').'%');
        }

        $rows = $query->limit(100)->get()->map(function (Folio $folio) {
            $payload = $folio->toApiArray();
            $payload['balance_cents'] = $this->folios->balanceCents($folio);
            $stay = Stay::on(config('tenancy.connection_name'))
                ->where('folio_id', $folio->getKey())
                ->orderByDesc('id')
                ->first();
            $payload['stay_id'] = $stay === null ? null : (int) $stay->getKey();

            return $payload;
        })->all();

        return response()->json(['results' => $rows], 200);
    }

    public function show(int $folio): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $model = Folio::on(config('tenancy.connection_name'))->find($folio);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json([
            'folio' => $model->toApiArray(),
            'charges' => Charge::on(config('tenancy.connection_name'))
                ->where('folio_id', $model->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (Charge $charge) => $charge->toApiArray())
                ->all(),
            'payments' => Payment::on(config('tenancy.connection_name'))
                ->where('folio_id', $model->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (Payment $payment) => $payment->toApiArray())
                ->all(),
            'voids' => FolioVoid::on(config('tenancy.connection_name'))
                ->where('folio_id', $model->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (FolioVoid $void) => $void->toApiArray())
                ->all(),
            'balance_cents' => $this->folios->balanceCents($model),
        ], 200);
    }

    public function invoice(int $folio): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $model = Folio::on(config('tenancy.connection_name'))->find($folio);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($this->invoices->payload($model), 200);
    }

    public function searchChargeToRoom(Request $request): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessChargeToRoom()) {
            return $denied;
        }

        $roomNumber = $request->query('room_number');
        $guest = $request->query('guest');

        $matches = $this->chargeToRoom->search(
            is_string($roomNumber) ? $roomNumber : null,
            is_string($guest) ? $guest : null,
        );

        return response()->json([
            'count' => count($matches),
            'results' => $matches,
        ], 200);
    }

    public function storeChargeToRoom(ChargeToRoomRequest $request): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessChargeToRoom()) {
            return $denied;
        }

        $data = $request->validated();
        $stayId = (int) $data['stay_id'];
        unset($data['stay_id']);

        $charge = $this->chargeToRoom->charge($stayId, $data);

        return response()->json($charge->toApiArray(), 201);
    }

    public function storeCharge(StoreChargeRequest $request, int $folio): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $model = Folio::on(config('tenancy.connection_name'))->find($folio);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $charge = $this->folios->postCharge($model, $request->validated());

        return response()->json($charge->toApiArray(), 201);
    }

    public function storePayment(StorePaymentRequest $request, int $folio): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessFolioClerk()) {
            return $denied;
        }

        $model = Folio::on(config('tenancy.connection_name'))->find($folio);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $payment = $this->folios->recordPayment($model, $request->validated());

        return response()->json($payment->toApiArray(), 201);
    }

    public function storeVoid(VoidRequest $request, int $folio): JsonResponse
    {
        if ($denied = FolioAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Folio::on(config('tenancy.connection_name'))->find($folio);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $void = $this->folios->voidEntry($model, $request->validated());

        return response()->json($void->toApiArray(), 201);
    }
}
