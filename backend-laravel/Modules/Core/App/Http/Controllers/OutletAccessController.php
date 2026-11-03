<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Http\Requests\StoreOutletAccessRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\StaffOutletAccess;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class OutletAccessController extends Controller
{
    use Paginates;

    public function index(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $q = StaffOutletAccess::on(config('tenancy.connection_name'))->orderBy('id');

        return $this->paginateQuery($q, $request, fn (StaffOutletAccess $a) => $this->serialize($a));
    }

    public function store(StoreOutletAccessRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $a = StaffOutletAccess::on(config('tenancy.connection_name'))->create($request->validated());
        CoreAudit::record(AuditEvent::ACTION_CREATE, 'outlet_access', (int) $a->getKey(), newValues: $this->serialize($a));

        return response()->json($this->serialize($a), 201);
    }

    public function destroy(int $outlet_access): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $a = StaffOutletAccess::on(config('tenancy.connection_name'))->findOrFail($outlet_access);
        $old = $this->serialize($a);
        $id = (int) $a->getKey();
        $a->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'outlet_access', $id, oldValues: $old, reason: 'deleted');

        return response()->json(null, 204);
    }

    private function serialize(StaffOutletAccess $a): array
    {
        return [
            'id' => $a->getKey(),
            'staff_id' => $a->staff_id,
            'outlet_id' => $a->outlet_id,
        ];
    }
}
