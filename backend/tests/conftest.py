"""pytest plugins for DB-per-tenant test suite.

The Django test runner / pytest-django resolves test databases from
settings.DATABASES before any test class runs.  Our tenant fixtures manipulate
connections.DATABASES at runtime (inside setUpClass), which is too late for the
runner's setup_databases.  Pre-declare tenant_1 / tenant_2 here so they exist
when the runner enumerates aliases, pointing at the central sqlite DB (the only
place pytest-django creates tables).
"""
from django.conf import settings
from django.db import connections
from tenancy.connections import ensure_tenant_connection

CENTRAL_NAME = settings.DATABASES['default']['NAME']
# Replicate the default TEST config Django injects as-is so alias checks
# (lookup of settings.DATABASES[alias]["TEST"]["MIRROR"]) don't KeyError.
DEFAULT_TEST = dict(settings.DATABASES['default'].get('TEST', {
    'CHARSET': None, 'COLLATION': None, 'MIGRATE': True,
    'MIRROR': None, 'NAME': None,
}))


def _register_tenant_alias(pk):
    alias = f'tenant_{pk}'
    if alias not in connections.databases:
        ensure_tenant_connection(
            type('s', (), {'pk': pk, 'db_name': 's'})()
        )
    cfg = connections.databases.setdefault(alias, {})
    cfg['NAME'] = CENTRAL_NAME
    cfg['TEST'] = dict(DEFAULT_TEST)
    cfg.setdefault('OPTIONS', {})
    settings.DATABASES.setdefault(alias, {})
    settings.DATABASES[alias]['NAME'] = CENTRAL_NAME
    settings.DATABASES[alias]['TEST'] = dict(DEFAULT_TEST)
    settings.DATABASES[alias].setdefault('OPTIONS', {})


for pk in (1, 2):
    _register_tenant_alias(pk)
