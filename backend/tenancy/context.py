"""
Current tenant context for the request.

Isolation is which DB connection is active, not a tenant_id row filter.
"""

import contextvars

_current_tenant: contextvars.ContextVar = contextvars.ContextVar(
    'current_tenant', default=None
)
_current_alias: contextvars.ContextVar = contextvars.ContextVar(
    'current_tenant_alias', default=None
)


def get_current_tenant():
    """Return the central Tenant registry row for this request, or None."""
    return _current_tenant.get()


def get_current_tenant_alias():
    """Django DATABASES alias for the active tenant DB, or None on central host."""
    return _current_alias.get()


def set_current_tenant(tenant, alias=None):
    _current_tenant.set(tenant)
    _current_alias.set(alias)


def clear_current_tenant():
    _current_tenant.set(None)
    _current_alias.set(None)


def is_central_request():
    """True when no tenant DB is active (saas / central host)."""
    return get_current_tenant() is None
