"""Session-based auth for Staff on the active tenant DB."""

from rest_framework.authentication import BaseAuthentication
from rest_framework.exceptions import AuthenticationFailed

from modules.core.models import Staff
from tenancy.context import get_current_tenant

SESSION_TENANT_ID = 'staff_tenant_id'
SESSION_TENANT_SLUG = 'staff_tenant_slug'


class StaffSessionAuthentication(BaseAuthentication):
    """
    Authenticates via staff_id in session against the tenant DB
    selected by TenantResolveMiddleware.

    BUG-TENANT-001 fix: the session must also carry the tenant binding
    written at login; requests on any other tenant host (missing or
    mismatched binding) are rejected before the Staff row is loaded.
    """

    def authenticate_header(self, request):
        # DRF returns 401 (not 403) for AuthenticationFailed when a header is present.
        return 'Session realm="api"'

    def authenticate(self, request):
        if getattr(request, 'is_central', False):
            return None
        session = getattr(request, 'session', None)
        if not session:
            return None
        staff_id = session.get('staff_id')
        if not staff_id:
            return None
        current_tenant = get_current_tenant()
        if current_tenant is None:
            # Tenant host resolved to nothing: cannot bind identity safely.
            raise AuthenticationFailed('Session tenant mismatch.')
        if session.get(SESSION_TENANT_ID) != current_tenant.pk:
            raise AuthenticationFailed('Session tenant mismatch.')
        if session.get(SESSION_TENANT_SLUG) != current_tenant.slug:
            raise AuthenticationFailed('Session tenant mismatch.')
        try:
            staff = Staff.objects.get(pk=staff_id)
        except Staff.DoesNotExist:
            raise AuthenticationFailed('Invalid session.')
        if not staff.is_active:
            raise AuthenticationFailed('Staff inactive.')
        if not current_tenant.is_active:
            raise AuthenticationFailed('Tenant inactive.')
        return (staff, None)
