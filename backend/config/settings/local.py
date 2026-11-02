"""
Local development settings.
"""
from .base import *  # noqa: F401,F403

DEBUG = True

ALLOWED_HOSTS = ['localhost', '127.0.0.1']

# Local dev: sqlite unless Postgres env vars are provided.
DATABASES = {
    'default': {
        'ENGINE': os.environ.get('DB_ENGINE', 'django.db.backends.sqlite3'),  # noqa: F405
        'NAME': os.environ.get('DB_NAME', BASE_DIR / 'db.sqlite3'),            # noqa: F405
        'USER': os.environ.get('DB_USER', ''),                                 # noqa: F405
        'PASSWORD': os.environ.get('DB_PASSWORD', ''),                         # noqa: F405
        'HOST': os.environ.get('DB_HOST', ''),                                 # noqa: F405
        'PORT': os.environ.get('DB_PORT', ''),                                 # noqa: F405
    }
}

# Allow API schema + admin in dev
INTERNAL_IPS = ['127.0.0.1']
