"""
Production settings (VPS). All secrets via env vars / .env on the server only.
"""
from .base import *  # noqa: F401,F403

DEBUG = False

ALLOWED_HOSTS = [
    h.strip() for h in os.environ.get('DJANGO_ALLOWED_HOSTS', '').split(',') if h.strip()
]

# Security hardening for production
SECURE_SSL_REDIRECT = os.environ.get('SECURE_SSL_REDIRECT', 'False') == 'True'
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
SECURE_BROWSER_XSS_FILTER = True
SECURE_CONTENT_TYPE_NOSNIFF = True
X_FRAME_OPTIONS = 'DENY'

# Require Postgres in production (no sqlite fallback)
DATABASES = {
    'default': {
        'ENGINE': os.environ.get('DB_ENGINE', 'django.db.backends.postgresql'),  # noqa: F405
        'NAME': os.environ.get('DB_NAME'),                                        # noqa: F405
        'USER': os.environ.get('DB_USER'),                                        # noqa: F405
        'PASSWORD': os.environ.get('DB_PASSWORD'),                                # noqa: F405
        'HOST': os.environ.get('DB_HOST'),                                        # noqa: F405
        'PORT': os.environ.get('DB_PORT', '5432'),                                # noqa: F405
    }
}
