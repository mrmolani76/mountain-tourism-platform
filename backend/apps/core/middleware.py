"""
TenantContextMiddleware — resolves the current tenant from the authenticated
staff user and exposes it to TenantManager via a context var.

Never trusts client-supplied tenant_id: tenant comes only from
request.user (the logged-in staff member's tenant).
"""

from .models.base import clear_current_tenant, set_current_tenant


class TenantContextMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        clear_current_tenant()
        user = getattr(request, 'user', None)
        if user is not None and getattr(user, 'is_authenticated', False):
            tenant = getattr(user, 'tenant', None)
            set_current_tenant(tenant)
        try:
            response = self.get_response(request)
        finally:
            clear_current_tenant()
        return response
