<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\App\Http\Requests\StoreOutletRequest;
use Modules\Core\App\Http\Requests\UpdateOutletRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class OutletController extends Controller
{
    use Paginates;

    public function index(Request $request): JsonResponse
    {
        $staff = StaffAuthz::staff();
        $q = Outlet::on(config('tenancy.connection_name'))->orderBy('name');

        if (! $staff->isOwner()) {
            $ids = $staff->outletAccess()->pluck('outlet_id');
            $q->whereIn('id', $ids);
        }

        return $this->paginateQuery($q, $request, fn (Outlet $o) => $this->serialize($o));
    }

    public function store(StoreOutletRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $data = $request->validated();
        if (! array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        $o = Outlet::on(config('tenancy.connection_name'))->create($data);

        CoreAudit::record(AuditEvent::ACTION_CREATE, 'outlet', (int) $o->getKey(), newValues: $this->serialize($o));

        return response()->json($this->serialize($o), 201);
    }

    public function show(int $outlet): JsonResponse
    {
        $o = Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet);

        if ($d = StaffAuthz::denyUnlessOutletAccess((int) $o->getKey())) {
            return $d;
        }

        return response()->json($this->serialize($o));
    }

    public function update(UpdateOutletRequest $request, int $outlet): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $o = Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet);
        $old = $this->serialize($o);
        $o->fill($request->validated())->save();

        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'outlet',
            (int) $o->getKey(),
            oldValues: $old,
            newValues: $this->serialize($o),
        );

        return response()->json($this->serialize($o));
    }

    public function destroy(int $outlet): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }

        $o = Outlet::on(config('tenancy.connection_name'))->findOrFail($outlet);
        $old = $this->serialize($o);
        $id = (int) $o->getKey();
        $o->delete();

        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'outlet', $id, oldValues: $old, reason: 'deleted');

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
