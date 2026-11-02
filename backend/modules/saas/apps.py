"""modules.saas — central registry (Complere Modules/Saas)."""

from django.apps import AppConfig


class SaasConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'modules.saas'
    label = 'saas'
    verbose_name = 'SaaS (central)'
