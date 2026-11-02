"""
Resolve tenant from Host header and activate its DB connection.

- Central hosts (saas.* / TENANCY_CENTRAL_HOSTS): no tenant DB; saas APIs only.
- Tenant hosts: Domain → Tenant on central DB → activate tenant_<id> connection.
"""

from django.conf import settings
from django.http import HttpResponseNotFound

from tenancy import connections, context


def _host_only(host: str) -> str:
    return (host or '').split(':')[0].strip().lower()


def _is_central_host(host: str) -> bool:
    central = getattr(settings, 'TENANCY_CENTRAL_HOSTS', ())
    if host in central:
        return True
    prefix = getattr(settings, 'TENANCY_CENTRAL_PREFIX', 'saas.')
    return host.startswith(prefix)


class TenantResolveMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        context.clear_current_tenant()
        host = _host_only(request.get_host())
        request.tenant = None
        request.tenant_db_alias = None

        if _is_central_host(host):
            request.is_central = True
            try:
                return self.get_response(request)
            finally:
                context.clear_current_tenant()

        request.is_central = False
        # Lookup Domain on central (default) DB only.
        from modules.saas.models import Domain

        try:
            domain = Domain.objects.select_related('tenant').get(
                hostname=host, is_primary=True
            )
        except Domain.DoesNotExist:
            return HttpResponseNotFound(b'Unknown tenant host.')

        tenant = domain.tenant
        if not tenant.is_active:
            return HttpResponseNotFound(b'Tenant inactive.')

        alias = connections.activate_tenant(tenant)
        context.set_current_tenant(tenant, alias=alias)
        request.tenant = tenant
        request.tenant_db_alias = alias
        try:
            return self.get_response(request)
        finally:
            context.clear_current_tenant()
