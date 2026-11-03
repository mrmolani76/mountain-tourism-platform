<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Http\Requests\LoginRequest;
use Modules\Core\App\Http\Requests\ResetPasswordRequest;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\Staff;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Support\StaffAuthz;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        $staff = Staff::on(config('tenancy.connection_name'))
            ->where('email', $validated['email'])
            ->first();

        if ($staff === null || ! Hash::check($validated['password'], (string) $staff->getAttribute('password'))) {
            return Envelope::errorJson('error', 'Invalid credentials.', [], 401);
        }

        if (! $staff->is_active) {
            return Envelope::errorJson('error', 'Staff inactive.', [], 403);
        }

        if ($tenant === null) {
            return Envelope::errorJson('error', 'Staff login requires a tenant host.', [], 401);
        }

        if (! $tenant->is_active) {
            return Envelope::errorJson('error', 'Tenant inactive.', [], 403);
        }

        $request->session()->regenerate();
        $request->session()->put('staff_id', $staff->getKey());
        $request->session()->put('staff_email', $staff->getAttribute('email'));
        $request->session()->put('staff_tenant_id', $tenant->getKey());
        $request->session()->put('staff_tenant_slug', $tenant->getAttribute('slug'));

        // Bind staff early so CoreAudit can attribute the login row.
        app()->instance('staff', $staff);
        CoreAudit::record(AuditEvent::ACTION_LOGIN, 'staff', (int) $staff->getKey());

        return response()->json($this->mePayload($staff, $tenant), 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return response()->json(['detail' => 'Logged out.'], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $staff = app('staff');
        $tenant = app('tenant');

        return response()->json($this->mePayload($staff, $tenant), 200);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $target = Staff::on(config('tenancy.connection_name'))
            ->find((int) $request->validated('staff_id'));

        if ($target === null) {
            return Envelope::errorJson('error', 'Staff not found.', [], 404);
        }

        $target->setAttribute('password', Hash::make($request->validated('new_password')));
        $target->save();

        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'staff',
            (int) $target->getKey(),
            reason: 'password_reset',
            newValues: ['id' => $target->getKey(), 'email' => $target->email],
        );

        return response()->json(['detail' => 'Password reset.'], 200);
    }

    private function mePayload(Staff $staff, object $tenant): array
    {
        $allOutlets = $staff->isOwner();
        $outletIds = $allOutlets
            ? Outlet::on(config('tenancy.connection_name'))->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $staff->outletAccess()->orderBy('outlet_id')->pluck('outlet_id')->map(fn ($id) => (int) $id)->all();

        return [
            'id' => $staff->getKey(),
            'email' => $staff->getAttribute('email'),
            'role' => $staff->roleDisplay(),
            'role_code' => $staff->getAttribute('role'),
            'is_active' => (bool) $staff->getAttribute('is_active'),
            'tenant_slug' => $tenant->getAttribute('slug'),
            'all_outlets' => $allOutlets,
            'outlet_ids' => $outletIds,
        ];
    }
}
