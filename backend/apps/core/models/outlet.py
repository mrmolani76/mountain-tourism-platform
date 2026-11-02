"""Outlet (business unit in a building). Stub here; expanded in item 06."""

from django.db import models

from .base import TenantManager


class Outlet(models.Model):
    name = models.CharField(max_length=200)
    tenant = models.ForeignKey(
        'core.Tenant',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='outlets',
    )
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = TenantManager()

    class Meta:
        db_table = 'core_outlet'
        ordering = ['name']

    def __str__(self):
        return self.name
