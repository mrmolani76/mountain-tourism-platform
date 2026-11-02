"""
Tenant isolation layer — single mechanism for all tenant-owned queries.

Every tenant-owned model inherits from TenantModel, which adds a `tenant`
FK (column `tenant_id`) and uses TenantManager. TenantManager always
filters by the current tenant context, resolved from the authenticated
staff user via TenantContextMiddleware. Client-supplied tenant_id is never
trusted for authorization.

Current tenant is stored in a context var so it is thread/async safe.
"""

import contextvars
from django.db import models

_current_tenant: contextvars.ContextVar = contextvars.ContextVar(
    'current_tenant', default=None
)


def get_current_tenant():
    """Return the Tenant for the current request/thread, or None."""
    return _current_tenant.get()


def set_current_tenant(tenant):
    _current_tenant.set(tenant)


def clear_current_tenant():
    _current_tenant.set(None)


class TenantManager(models.Manager):
    """Single isolation layer: scopes every queryset to the current tenant.

    When no tenant context is set (e.g. no authenticated staff), returns
    an empty queryset so tenant-owned data is never leaked.
    """

    def get_queryset(self):
        qs = super().get_queryset()
        tenant = get_current_tenant()
        if tenant is None:
            return qs.none()
        return qs.filter(tenant=tenant)


class TenantModel(models.Model):
    """Abstract base for every tenant-owned table.

    Subclasses automatically get `tenant` FK (column `tenant_id`) and
    TenantManager as the default manager, enforcing isolation in one place.
    """

    tenant = models.ForeignKey(
        'core.Tenant',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='%(class)s_set',
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = TenantManager()

    class Meta:
        abstract = True
