"""TenantSettings (tenant DB). One row per complex, singleton style."""

from django.db import models


class Units(models.TextChoices):
    METRIC = 'metric', 'Metric'
    IMPERIAL = 'imperial', 'Imperial'


class TenantSettings(models.Model):
    """One settings row per tenant DB (created lazily by the service layer)."""

    currency = models.CharField(max_length=8, default='EUR')
    tax_rates = models.JSONField(default=list, blank=True)
    timezone = models.CharField(max_length=64, default='UTC')
    units = models.CharField(
        max_length=16,
        choices=Units.choices,
        default=Units.METRIC,
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_tenant_settings'

    def __str__(self):
        return f'TenantSettings ({self.currency})'
