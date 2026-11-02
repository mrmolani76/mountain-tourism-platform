"""Service — bookable service (ski school, guided hike, spa, etc.) (Module F)."""

from django.db import models


class Service(models.Model):
    """A bookable service offered by an outlet."""

    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.PROTECT,
        related_name='services',
    )
    name = models.CharField(max_length=200)
    description = models.CharField(max_length=255, blank=True, default='')
    duration_minutes = models.PositiveIntegerField(default=60)
    price = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'services_service'
        ordering = ['outlet', 'name']

    def __str__(self):
        return f'{self.outlet.name} — {self.name}'
