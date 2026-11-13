<?php

namespace Modules\Transport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transport\App\Http\Requests\SellLiftPassRequest;
use Modules\Transport\App\Http\Requests\StoreLiftPassProductRequest;
use Modules\Transport\App\Models\LiftPassProduct;
use Modules\Transport\App\Services\LiftPassService;
use Modules\Transport\App\Support\TransportAuthz;

class LiftPassController extends Controller
{
    public function __construct(private readonly LiftPassService $passes)
    {
    }

    public function products(): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessPassSeller()) {
            return $denied;
        }

        $rows = LiftPassProduct::on(config('tenancy.connection_name'))
            ->orderBy('id')
            ->get()
            ->map(fn (LiftPassProduct $p) => $p->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function storeProduct(StoreLiftPassProductRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $product = LiftPassProduct::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($product->toApiArray(), 201);
    }

    public function sell(SellLiftPassRequest $request): JsonResponse
    {
        if ($denied = TransportAuthz::denyUnlessPassSeller()) {
            return $denied;
        }

        $pass = $this->passes->sell($request->validated());

        return response()->json($pass->toApiArray(), 201);
    }
}
