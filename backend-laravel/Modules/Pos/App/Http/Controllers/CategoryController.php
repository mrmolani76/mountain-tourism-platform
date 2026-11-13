<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Pos\App\Http\Requests\StoreCategoryRequest;
use Modules\Pos\App\Models\Category;
use Modules\Pos\App\Support\PosAuthz;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $rows = Category::on(config('tenancy.connection_name'))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Category $c) => $c->toApiArray())
            ->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $category = Category::on(config('tenancy.connection_name'))->create($request->validated());

        return response()->json($category->toApiArray(), 201);
    }

    public function show(int $category): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = Category::on(config('tenancy.connection_name'))->find($category);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json($model->toApiArray(), 200);
    }

    public function update(StoreCategoryRequest $request, int $category): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $model = Category::on(config('tenancy.connection_name'))->find($category);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($model->fresh()->toApiArray(), 200);
    }
}
