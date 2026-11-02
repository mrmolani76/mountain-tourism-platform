"""Slot — one time slot for a service, with capacity and staff (Module F)."""

from django.db import models


class Slot(models.Model):
    """One time slot for a service with capacity and assigned staff."""

    service = models.ForeignKey(
        'services.Service',
        on_delete=models.CASCADE,
        related_name='slots',
    )
    starts_at = models.DateTimeField()
    ends_at = models.DateTimeField()
    capacity = models.PositiveIntegerField(default=1)
    booked_count = models.PositiveIntegerField(default=0)
    staff = models.ForeignKey(
        'core.Staff',
        on_delete=models.PROTECT,
        related_name='service_slots',
    )
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'services_slot'
        ordering = ['starts_at']

    def __str__(self):
        return f'Slot #{self.pk} — {self.service.name} ({self.starts_at})'

    @property
    def available_capacity(self):
        return self.capacity - self.booked_count

    @property
    def is_full(self):
        return self.booked_count >= self.capacity
