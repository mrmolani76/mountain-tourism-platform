"""Session-based auth for Staff on the active tenant DB."""

from rest_framework.authentication import BaseAuthentication
from rest_framework.exceptions import AuthenticationFailed

from modules.core.models import Staff
from tenancy.context import get_current_tenant


class StaffSessionAuthentication(BaseAuthentication):
    """
    Authenticates via staff_id in session against the tenant DB
    selected by TenantResolveMiddleware.
    """

    def authenticate(self, request):
        if getattr(request, 'is_central', False):
            return None
        session = getattr(request, 'session', None)
        if not session:
            return None
        staff_id = session.get('staff_id')
        if not staff_id:
            return None
        try:
            staff = Staff.objects.get(pk=staff_id)
        except Staff.DoesNotExist:
            raise AuthenticationFailed('Invalid session.')
        if not staff.is_active:
            raise AuthenticationFailed('Staff inactive.')
        tenant = get_current_tenant()
        if tenant is not None and not tenant.is_active:
            raise AuthenticationFailed('Tenant inactive.')
        return (staff, None)
