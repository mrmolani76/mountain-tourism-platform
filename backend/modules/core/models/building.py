"""Building (tenant DB). Anchor point for outlets and later weather readings."""

from django.db import models


class BuildingType(models.TextChoices):
    """Building kinds from PDF §2 demo layout."""

    VALLEY_STATION = 'valley_station', 'Valley Station'
    MAIN_HOTEL = 'main_hotel', 'Main Hotel'
    CHALETS = 'chalets', 'Chalets'
    MID_STATION = 'mid_station', 'Mid Station'
    MOUNTAIN_HUT = 'mountain_hut', 'Mountain Hut'
    SUMMIT_STATION = 'summit_station', 'Summit Station'
    OTHER = 'other', 'Other'


class Building(models.Model):
    name = models.CharField(max_length=200)
    building_type = models.CharField(
        max_length=32,
        choices=BuildingType.choices,
        default=BuildingType.OTHER,
    )
    altitude_m = models.IntegerField(null=True, blank=True)
    latitude = models.DecimalField(max_digits=9, decimal_places=6, null=True, blank=True)
    longitude = models.DecimalField(max_digits=9, decimal_places=6, null=True, blank=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_building'
        ordering = ['altitude_m', 'name']

    def __str__(self):
        return self.name
