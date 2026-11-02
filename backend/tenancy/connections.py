"""
Register and activate per-tenant database connections.

Code only: helpers do not create Postgres databases. Callers / ops run setup later.
"""

from copy import deepcopy

from django.conf import settings
from django.db import connections


def tenant_alias(tenant_id: int) -> str:
    return f'tenant_{tenant_id}'


def ensure_tenant_connection(tenant) -> str:
    """
    Ensure settings.DATABASES has an alias for this tenant and return the alias.

    Uses TENANT_DB_TEMPLATE from settings (clone of default with NAME overridden).
    Does not create the physical database.
    """
    alias = tenant_alias(tenant.pk)
    if alias not in settings.DATABASES:
        template = getattr(settings, 'TENANT_DB_TEMPLATE', None)
        if template is None:
            template = settings.DATABASES['default']
        cfg = deepcopy(template)
        cfg['NAME'] = tenant.db_name
        settings.DATABASES[alias] = cfg
    # Force Django to notice a new alias if connections were already loaded.
    if alias not in connections.databases:
        connections.databases[alias] = settings.DATABASES[alias]
    return alias


def activate_tenant(tenant) -> str:
    """Register connection for tenant and return alias. Pair with context.set_current_tenant."""
    return ensure_tenant_connection(tenant)
