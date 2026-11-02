"""Outlet stub (tenant DB). Expanded in item 06."""

from django.db import models


class Outlet(models.Model):
    name = models.CharField(max_length=200)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_outlet'
        ordering = ['name']

    def __str__(self):
        return self.name
