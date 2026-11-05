<?php

namespace Modules\Folio\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;
use Modules\Folio\App\Http\Requests\StoreChargeRequest;
use Modules\Folio\App\Http\Requests\StorePaymentRequest;
use Modules\Folio\App\Http\Requests\VoidRequest;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Support\HotelAuthz;

class FolioController extends Controller
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    public function show(int $folio): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFolioClerk()) {
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

    public function storeCharge(StoreChargeRequest $request, int $folio): JsonResponse
    {
        if ($denied = HotelAuthz::denyUnlessFolioClerk()) {
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
        if ($denied = HotelAuthz::denyUnlessFolioClerk()) {
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
        if ($denied = HotelAuthz::denyUnlessManagerPlus()) {
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
