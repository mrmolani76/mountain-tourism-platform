"""Configuration project settings shortcut.

Keeps `DJANGO_SETTINGS_MODULE=config.settings` working for tooling (CI, pytest)
by defaulting to the local development settings.
"""

from .local import *  # noqa: F401,F403
