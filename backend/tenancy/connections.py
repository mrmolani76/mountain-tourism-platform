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

    BUG-ENV-001: aliases registered after Django's ConnectionHandler configured
    the default alias never get backend defaults (OPTIONS, TIME_ZONE, ...), so
    connections crash. We merge the standard defaults explicitly.
    """
    alias = tenant_alias(tenant.pk)
    if alias not in settings.DATABASES:
        template = getattr(settings, 'TENANT_DB_TEMPLATE', None)
        if template is None:
            template = settings.DATABASES['default']
        cfg = deepcopy(template)
        cfg['NAME'] = tenant.db_name
        _apply_backend_defaults(cfg)
        settings.DATABASES[alias] = cfg
    # Force Django to notice a new alias if connections were already loaded.
    if alias not in connections.databases:
        registered = deepcopy(settings.DATABASES[alias])
        _apply_backend_defaults(registered)
        connections.databases[alias] = registered
    return alias


# Defaults Django normally injects at handler init; late aliases need them too.
def _apply_backend_defaults(cfg: dict) -> None:
    """Fill standard DB settings defaults without overriding explicit values."""
    defaults = {
        'OPTIONS': {},
        'TIME_ZONE': None,
        'CONN_MAX_AGE': 0,
        'CONN_HEALTH_CHECKS': False,
        'AUTOCOMMIT': True,
        'ATOMIC_REQUESTS': False,
        'TEST': {},
    }
    for key, value in defaults.items():
        cfg.setdefault(key, deepcopy(value))


def activate_tenant(tenant) -> str:
    """Register connection for tenant and return alias. Pair with context.set_current_tenant."""
    return ensure_tenant_connection(tenant)
