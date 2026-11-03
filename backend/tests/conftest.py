"""pytest plugins for the DB-per-tenant suite.

The test classes create Tenant rows with EXPLICIT primary keys (id=1, id=2), so
the tenant DB aliases are always `tenant_1` / `tenant_2`. Pre-register those
aliases here (pointing at the physical, already-migrated scratch DBs) before
Django's setup_databases runs, and declare them in each class's `databases`.

Do NOT register aliases for other pks and do NOT use `databases = '__all__'`:
Django takes the allowed-alias snapshot at class setup time, and aliases
created later are blocked (and break teardown unwrap).
"""
from django.conf import settings
from django.db import connections

from tenancy.connections import _apply_backend_defaults

CENTRAL_NAME = settings.DATABASES['default']['NAME']
DEFAULT_TEST = dict(settings.DATABASES['default'].get('TEST', {
    'CHARSET': None, 'COLLATION': None, 'MIGRATE': True,
    'MIRROR': None, 'NAME': None,
}))

# alias -> physical scratch DB (both already migrated; tests roll back)
_ALIAS_DB = {
    'tenant_1': 'lab_test',
    'tenant_2': 'other_test',
}

for alias, db_name in _ALIAS_DB.items():
    cfg = connections.databases.setdefault(alias, {})
    cfg.update({
        'ENGINE': settings.DATABASES['default']['ENGINE'],
        'NAME': db_name,
        'USER': settings.DATABASES['default'].get('USER', ''),
        'PASSWORD': settings.DATABASES['default'].get('PASSWORD', ''),
        'HOST': settings.DATABASES['default'].get('HOST', ''),
        'PORT': settings.DATABASES['default'].get('PORT', ''),
        'OPTIONS': {},
        'TEST': dict(DEFAULT_TEST, MIRROR=None),
    })
    sc = settings.DATABASES.setdefault(alias, {})
    sc.update(cfg)
    _apply_backend_defaults(sc)
    _apply_backend_defaults(connections.databases[alias])
