<?php

namespace Modules\Core\App\Http\Controllers;

use App\Support\Http\Envelope;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\Outlet;

class OutletController
{
    use Paginates;

    private function ensureOwner(): ?JsonResponse
    {
        if (! app('staff')->isOwner()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $q = Outlet::on(config('tenancy.connection_name'))->orderBy('name');
        return $this->paginateQuery($q, $request, fn (Outlet $o) => $this->serialize($o));
    }

    public function store(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $data = $request->validate([
            'building_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:200'],
            'outlet_type' => ['required', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $o = Outlet::on(config('tenancy.connection_name'))->create($data);
        return response()->json($this->serialize($o), 201);
    }

    public function show(int $outlet): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $o = Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet);
        return response()->json($this->serialize($o));
    }

    public function update(Request $request, int $outlet): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $o = Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet);
        $data = $request->validate([
            'building_id' => ['sometimes', 'integer'],
            'name' => ['sometimes', 'string', 'max:200'],
            'outlet_type' => ['sometimes', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $o->fill($data)->save();
        return response()->json($this->serialize($o));
    }

    public function destroy(int $outlet): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet)->delete();
        return response()->json(null, 204);
    }

    private function serialize(Outlet $o): array
    {
        return [
            'id' => $o->getKey(),
            'building_id' => $o->building_id,
            'name' => $o->name,
            'outlet_type' => $o->outlet_type,
            'is_active' => (bool) $o->is_active,
        ];
    }
}
