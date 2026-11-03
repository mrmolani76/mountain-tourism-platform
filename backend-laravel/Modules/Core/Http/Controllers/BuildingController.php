<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Http\Requests\StoreBuildingRequest;
use Modules\Core\Http\Requests\UpdateBuildingRequest;
use Modules\Core\Models\Building;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingController
{
    private function ensureOwner(): ?JsonResponse
    {
        $staff = app('staff');

        if (! $staff->isOwner()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->ensureOwner()) {
            return $denied;
        }

        $page = max(1, (int) $request->query('page', 1));
        $size = (int) $request->query('page_size', 20);
        if ($size < 1) {
            $size = 20;
        }
        if ($size > 100) {
            $size = 100;
        }

        $query = Building::on(config('tenancy.connection_name'))
            ->orderByRaw('altitude_m asc nulls first')
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = (clone $query)->skip(($page - 1) * $size)->take($size)->get();

        $totalPages = (int) ceil($total / $size);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        $data = $items->map(fn (Building $b) => $this->serialize($b))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'error' => null,
            'meta' => [
                'page' => $page,
                'page_size' => $size,
                'total_pages' => $totalPages,
                'total_count' => $total,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1,
            ],
        ], 200);
    }

    public function show(int $building): JsonResponse
    {
        if ($denied = $this->ensureOwner()) {
            return $denied;
        }

        $model = Building::on(config('tenancy.connection_name'))->find($building);

        if ($model === null) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

        return response()->json($this->serialize($model), 200);
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        if ($denied = $this->ensureOwner()) {
            return $denied;
        }

        $data = $request->validated();

        $model = new Building();
        $model->setConnection(config('tenancy.connection_name'));
        $model->fill($data);
        if (! array_key_exists('building_type', $data)) {
            $model->setAttribute('building_type', 'other');
        }
        if (! array_key_exists('is_active', $data)) {
            $model->setAttribute('is_active', true);
        }
        $model->save();

        return response()->json($this->serialize($model->fresh()), 201);
    }

    public function update(UpdateBuildingRequest $request, int $building): JsonResponse
    {
        if ($denied = $this->ensureOwner()) {
            return $denied;
        }

        $model = Building::on(config('tenancy.connection_name'))->find($building);

        if ($model === null) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

        $model->fill($request->validated());
        $model->save();

        return response()->json($this->serialize($model->fresh()), 200);
    }

    public function destroy(int $building): \Illuminate\Http\Response
    {
        if ($denied = $this->ensureOwner()) {
            return $denied;
        }

        $model = Building::on(config('tenancy.connection_name'))->find($building);

        if ($model === null) {
            return Envelope::errorJson('error', 'Not found.', [], 404);
        }

        $model->delete();

        return response()->noContent();
    }

    private function serialize(Building $b): array
    {
        return [
            'id' => $b->getKey(),
            'name' => $b->getAttribute('name'),
            'building_type' => $b->getAttribute('building_type'),
            'altitude_m' => $b->getAttribute('altitude_m'),
            'latitude' => $b->getAttribute('latitude'),
            'longitude' => $b->getAttribute('longitude'),
            'is_active' => (bool) $b->getAttribute('is_active'),
            'created_at' => $b->getAttribute('created_at')?->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $b->getAttribute('updated_at')?->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }
}
