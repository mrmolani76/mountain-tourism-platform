<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pos\App\Http\Requests\StoreOutletProductRequest;
use Modules\Pos\App\Http\Requests\StoreProductRequest;
use Modules\Pos\App\Http\Requests\StoreProductSeasonRequest;
use Modules\Pos\App\Models\OutletProduct;
use Modules\Pos\App\Models\Product;
use Modules\Pos\App\Models\ProductSeason;
use Modules\Pos\App\Support\PosAuthz;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $query = Product::on(config('tenancy.connection_name'))->orderBy('id');
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->query('category_id'));
        }
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        $rows = $query->get()->map(fn (Product $p) => $p->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $product = Product::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($product->toApiArray(), 201);
    }

    public function show(int $product): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = Product::on(config('tenancy.connection_name'))->find($product);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function update(StoreProductRequest $request, int $product): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Product::on(config('tenancy.connection_name'))->find($product);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }

    public function attachOutlet(StoreOutletProductRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $row = OutletProduct::on(config('tenancy.connection_name'))->updateOrCreate(
            [
                'outlet_id' => (int) $request->validated('outlet_id'),
                'product_id' => (int) $request->validated('product_id'),
            ],
            [
                'is_available' => $request->validated('is_available') ?? true,
                'sort_order' => $request->validated('sort_order') ?? 0,
            ]
        );

        return response()->json($row->toApiArray(), 201);
    }

    public function attachSeason(StoreProductSeasonRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $row = ProductSeason::on(config('tenancy.connection_name'))->updateOrCreate(
            [
                'product_id' => (int) $request->validated('product_id'),
                'season_id' => (int) $request->validated('season_id'),
            ],
            [
                'is_available' => $request->validated('is_available') ?? true,
                'price_cents' => $request->validated('price_cents') ?? null,
            ]
        );

        return response()->json($row->toApiArray(), 201);
    }
}
