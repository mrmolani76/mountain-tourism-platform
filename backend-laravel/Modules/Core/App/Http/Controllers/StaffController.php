<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Paginates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Http\Requests\StoreStaffRequest;
use Modules\Core\App\Http\Requests\UpdateStaffRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Staff;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class StaffController extends Controller
{
    use Paginates;

    public function index(Request $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $q = Staff::on(config('tenancy.connection_name'))->orderBy('email');

        return $this->paginateQuery($q, $request, fn (Staff $s) => $this->serialize($s));
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        if (! array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }
        $s = Staff::on(config('tenancy.connection_name'))->create($data);
        CoreAudit::record(AuditEvent::ACTION_CREATE, 'staff', (int) $s->getKey(), newValues: $this->serialize($s));

        return response()->json($this->serialize($s), 201);
    }

    public function show(int $staff): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Staff::on(config('tenancy.connection_name'))->findOrFail($staff);

        return response()->json($this->serialize($s));
    }

    public function update(UpdateStaffRequest $request, int $staff): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Staff::on(config('tenancy.connection_name'))->findOrFail($staff);
        $old = $this->serialize($s);
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $s->fill($data)->save();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'staff',
            (int) $s->getKey(),
            oldValues: $old,
            newValues: $this->serialize($s),
        );

        return response()->json($this->serialize($s));
    }

    public function destroy(int $staff): JsonResponse
    {
        if ($d = StaffAuthz::denyUnlessOwner()) {
            return $d;
        }
        $s = Staff::on(config('tenancy.connection_name'))->findOrFail($staff);
        $old = $this->serialize($s);
        $id = (int) $s->getKey();
        $s->delete();
        CoreAudit::record(AuditEvent::ACTION_UPDATE, 'staff', $id, oldValues: $old, reason: 'deleted');

        return response()->json(null, 204);
    }

    private function serialize(Staff $s): array
    {
        return [
            'id' => $s->getKey(),
            'email' => $s->email,
            'role' => $s->role,
            'role_display' => $s->roleDisplay(),
            'is_active' => (bool) $s->is_active,
        ];
    }
}
