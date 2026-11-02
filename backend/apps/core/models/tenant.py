"""Tenant (one complex). Top-level table — not tenant-scoped."""

from django.db import models


class Tenant(models.Model):
    name = models.CharField(max_length=200)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'core_tenant'
        ordering = ['name']

    def __str__(self):
        return self.name
