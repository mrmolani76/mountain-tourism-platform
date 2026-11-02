"""Trip — one run of own transport: shuttle, taxi, pickup (Module G)."""

from django.db import models


class TripStatus(models.TextChoices):
    SCHEDULED = 'scheduled', 'Scheduled'
    IN_PROGRESS = 'in_progress', 'In Progress'
    COMPLETED = 'completed', 'Completed'
    CANCELLED = 'cancelled', 'Cancelled'


class Trip(models.Model):
    """One run of transport between buildings."""

    vehicle = models.ForeignKey(
        'transport.Vehicle',
        on_delete=models.PROTECT,
        related_name='trips',
    )
    driver = models.ForeignKey(
        'transport.Driver',
        on_delete=models.PROTECT,
        related_name='trips',
    )
    from_building = models.ForeignKey(
        'core.Building',
        on_delete=models.PROTECT,
        related_name='trips_from',
    )
    to_building = models.ForeignKey(
        'core.Building',
        on_delete=models.PROTECT,
        related_name='trips_to',
    )
    starts_at = models.DateTimeField()
    ends_at = models.DateTimeField(null=True, blank=True)
    status = models.CharField(
        max_length=16,
        choices=TripStatus.choices,
        default=TripStatus.SCHEDULED,
    )
    passenger_count = models.PositiveIntegerField(default=0)
    notes = models.CharField(max_length=255, blank=True, default='')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'transport_trip'
        ordering = ['-starts_at']

    def __str__(self):
        return f'Trip #{self.pk} — {self.from_building} → {self.to_building} ({self.status})'
