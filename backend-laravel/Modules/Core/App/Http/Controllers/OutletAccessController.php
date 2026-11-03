<?php

namespace Modules\Core\App\Http\Controllers;

use App\Support\Http\Envelope;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Models\StaffOutletAccess;

class OutletAccessController
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
        $q = StaffOutletAccess::on(config('tenancy.connection_name'))->orderBy('id');
        return $this->paginateQuery($q, $request, fn (StaffOutletAccess $a) => [
            'id' => $a->getKey(),
            'staff_id' => $a->staff_id,
            'outlet_id' => $a->outlet_id,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        $data = $request->validate([
            'staff_id' => ['required', 'integer'],
            'outlet_id' => ['required', 'integer'],
        ]);
        $a = StaffOutletAccess::on(config('tenancy.connection_name'))->create($data);
        return response()->json([
            'id' => $a->getKey(),
            'staff_id' => $a->staff_id,
            'outlet_id' => $a->outlet_id,
        ], 201);
    }

    public function destroy(int $outlet_access): JsonResponse
    {
        if ($d = $this->ensureOwner()) return $d;
        StaffOutletAccess::on(config('tenancy.connection_name'))->findOrFail($outlet_access)->delete();
        return response()->json(null, 204);
    }
}
