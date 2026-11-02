"""Minimal staff record for item 04 isolation test.

Item 05 expands this (roles, outlet access, password reset, login API).
"""

from django.db import models

from .base import TenantManager


class Staff(models.Model):
    email = models.EmailField(max_length=254, unique=True)
    password = models.CharField(max_length=128)  # hashed, no plaintext
    tenant = models.ForeignKey(
        'core.Tenant',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='staff_set',
    )
    is_active = models.BooleanField(default=True)
    last_login = models.DateTimeField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = TenantManager()

    class Meta:
        db_table = 'core_staff'
        ordering = ['email']

    def __str__(self):
        return self.email

    # Mimic Django user so DRF SessionAuthentication / force_login work.
    @property
    def is_authenticated(self):
        return True

    @property
    def is_anonymous(self):
        return False
