"""
Local development settings.
"""
from .base import *  # noqa: F401,F403

import os

DEBUG = True

# Env hosts first (docker sets its own), plus local default suffix hosts.
_env_hosts = [h.strip() for h in os.environ.get('DJANGO_ALLOWED_HOSTS', '').split(',') if h.strip()]
ALLOWED_HOSTS = list(dict.fromkeys([
    *_env_hosts,
    'localhost',
    '127.0.0.1',
    'saas.local.mountain.local',
    '.local.mountain.local',
]))

# Local: sqlite central unless Postgres env vars are provided.
# Per-tenant DBs are registered at runtime via TENANT_DB_TEMPLATE (same engine).
DATABASES = {
    'default': {
        'ENGINE': os.environ.get('DB_ENGINE', 'django.db.backends.sqlite3'),  # noqa: F405
        'NAME': os.environ.get('DB_NAME', BASE_DIR / 'central.sqlite3'),      # noqa: F405
        'USER': os.environ.get('DB_USER', ''),                                 # noqa: F405
        'PASSWORD': os.environ.get('DB_PASSWORD', ''),                         # noqa: F405
        'HOST': os.environ.get('DB_HOST', ''),                                 # noqa: F405
        'PORT': os.environ.get('DB_PORT', ''),                                 # noqa: F405
    }
}

TENANT_DB_TEMPLATE = {
    'ENGINE': DATABASES['default']['ENGINE'],
    # Postgres needs a valid NAME (overridden per tenant anyway); sqlite falls back.
    'NAME': DATABASES['default']['NAME'] or (BASE_DIR / 'tenant_template.sqlite3'),  # noqa: F405
    'USER': DATABASES['default'].get('USER', ''),
    'PASSWORD': DATABASES['default'].get('PASSWORD', ''),
    'HOST': DATABASES['default'].get('HOST', ''),
    'PORT': DATABASES['default'].get('PORT', ''),
}

INTERNAL_IPS = ['127.0.0.1']
