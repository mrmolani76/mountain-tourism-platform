"""Outlet (tenant DB). Business unit inside a building."""

from django.db import models


class OutletType(models.TextChoices):
    """Outlet kinds from PDF §5A (outlets across the complex)."""

    RESTAURANT = 'restaurant', 'Restaurant'
    SELF_SERVICE = 'self_service', 'Self Service'
    HUT_KITCHEN = 'hut_kitchen', 'Hut Kitchen'
    PANORAMA_RESTAURANT = 'panorama_restaurant', 'Panorama Restaurant'
    SHOP = 'shop', 'Shop'
    RENTAL = 'rental', 'Rental'
    SPA = 'spa', 'Spa'
    SAUNA = 'sauna', 'Sauna'
    GYM = 'gym', 'Gym'
    SKI_SCHOOL = 'ski_school', 'Ski School'
    RECEPTION = 'reception', 'Reception'
    TICKETS = 'tickets', 'Tickets'
    SPORTS = 'sports', 'Sports'
    TRANSPORT = 'transport', 'Transport'
    LIFTS = 'lifts', 'Lifts'
    OTHER = 'other', 'Other'


class Outlet(models.Model):
    building = models.ForeignKey(
        'core.Building',
        on_delete=models.PROTECT,
        related_name='outlets',
    )
    name = models.CharField(max_length=200)
    outlet_type = models.CharField(
        max_length=32,
        choices=OutletType.choices,
        default=OutletType.OTHER,
    )
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_outlet'
        ordering = ['name']

    def __str__(self):
        return self.name
