<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Core\App\Http\Requests\LoginRequest;
use Modules\Core\App\Models\Staff;
use App\Support\Http\Envelope;
use Modules\Core\App\Support\StaffAuthz;
use App\Tenancy\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Registry always hit the central connection first.
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
            // Staff login is a tenant-host flow only (BUG-TENANT-001).
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

        // DRF parity: last_login is never updated on login.

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

    public function resetPassword(Request $request): JsonResponse
    {
        if ($denied = StaffAuthz::denyUnlessManagerPlus()) {
            return $denied;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'staff_id' => ['required', 'integer'],
            'new_password' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        if ($validator->fails()) {
            return Envelope::errorJson('error', 'Validation failed.', $validator->errors()->toArray(), 400);
        }

        $target = Staff::on(config('tenancy.connection_name'))
            ->find((int) $request->input('staff_id'));

        if ($target === null) {
            return Envelope::errorJson('error', 'Staff not found.', [], 404);
        }

        $target->setAttribute('password', Hash::make($request->input('new_password')));
        $target->save();

        return response()->json(['detail' => 'Password reset.'], 200);
    }

    private function mePayload(Staff $staff, object $tenant): array
    {
        return [
            'id' => $staff->getKey(),
            'email' => $staff->getAttribute('email'),
            'role' => $staff->roleDisplay(),
            'role_code' => $staff->getAttribute('role'),
            'is_active' => (bool) $staff->getAttribute('is_active'),
            'tenant_slug' => $tenant->getAttribute('slug'),
        ];
    }
}
