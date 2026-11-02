"""Vehicle — shuttle, taxi, pickup (Module G — transport)."""

from django.db import models


class VehicleType(models.TextChoices):
    SHUTTLE = 'shuttle', 'Shuttle'
    TAXI = 'taxi', 'Taxi'
    PICKUP = 'pickup', 'Pickup'


class Vehicle(models.Model):
    """One vehicle owned by the complex."""

    vehicle_type = models.CharField(
        max_length=16,
        choices=VehicleType.choices,
        default=VehicleType.SHUTTLE,
    )
    license_plate = models.CharField(max_length=20, unique=True)
    capacity = models.PositiveIntegerField(default=4)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'transport_vehicle'
        ordering = ['license_plate']

    def __str__(self):
        return f'{self.license_plate} ({self.vehicle_type})'
