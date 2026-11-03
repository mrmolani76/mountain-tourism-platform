<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use App\Support\Http\Envelope;
use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Staff session authentication with tenant binding — port of Django
 * `modules/core/auth.py::StaffSessionAuthentication` (audit §4):
 *
 * - session keys: staff_id, staff_tenant_id, staff_tenant_slug
 * - session tenant MUST match resolved tenant (BUG-TENANT-001 guard)
 * - unknown/inactive staff, inactive tenant → fail
 * - tenant hosts: unauthenticated → 401 (authenticate_header parity)
 * - central hosts: 403 (DRF behaviour)
 */
class StaffSessionAuth
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        if (TenantResolver::isCentralHost($host) === true) {
            return Envelope::errorJson(
                'error', 'You do not have permission to perform this action.', [], 403
            );
        }

        $tenant = TenantResolver::resolve($host);

        if ($tenant === null) {
            return Envelope::errorJson(
                'error', 'You do not have permission to perform this action.', [], 403
            );
        }

        $session = $request->session();
        $staffId = $session->get('staff_id');

        if ($staffId === null) {
            return Envelope::errorJson(
                'error', 'Authentication credentials were not provided.', [], 401
            );
        }

        $boundTenantId = $session->get('staff_tenant_id');
        $boundTenantSlug = $session->get('staff_tenant_slug');

        if ($boundTenantId !== $tenant->getKey()
            || $boundTenantSlug !== (string) $tenant->getAttribute('slug')) {
            return Envelope::errorJson('error', 'Session tenant mismatch.', [], 401);
        }

        $staff = Staff::on(config('tenancy.connection_name'))
            
            ->where('email', (string) $session->get('staff_email', ''))
            ->where('id', (int) $staffId)
            ->first();

        if ($staff === null) {
            $session->forget(['staff_id', 'staff_email', 'staff_tenant_id', 'staff_tenant_slug']);
            return Envelope::errorJson('error', 'Invalid session.', [], 401);
        }

        if (! $staff->is_active) {
            $session->forget(['staff_id', 'staff_email', 'staff_tenant_id', 'staff_tenant_slug']);
            return Envelope::errorJson('error', 'Staff inactive.', [], 401);
        }

        if (! $tenant->is_active) {
            return Envelope::errorJson('error', 'Tenant inactive.', [], 401);
        }

        app()->instance('staff', $staff);

        return $next($request);
    }
}
