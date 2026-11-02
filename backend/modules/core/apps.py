"""modules.core — tenant foundation (Complere Modules/Core)."""

from django.apps import AppConfig


class CoreConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'modules.core'
    label = 'core'
    verbose_name = 'Core'
