"""Driver — assigned to vehicles (Module G — transport). Drivers don't log in (MVP)."""

from django.db import models


class Driver(models.Model):
    """Driver for transport vehicles. No login in MVP."""

    name = models.CharField(max_length=200)
    phone = models.CharField(max_length=50, blank=True, default='')
    license_number = models.CharField(max_length=50, blank=True, default='')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'transport_driver'
        ordering = ['name']

    def __str__(self):
        return self.name
