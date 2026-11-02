"""Rental — one rented item from out to return (Module E)."""

from django.db import models


class RentalStatus(models.TextChoices):
    OUT = 'out', 'Out'
    RETURNED = 'returned', 'Returned'
    DAMAGED = 'damaged', 'Damaged'


class Rental(models.Model):
    """One rented item. From checkout to return, with optional damage fee."""

    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.PROTECT,
        related_name='rentals',
    )
    staff = models.ForeignKey(
        'core.Staff',
        on_delete=models.PROTECT,
        related_name='rentals',
    )
    folio = models.ForeignKey(
        'folio.Folio',
        on_delete=models.SET_NULL,
        null=True, blank=True,
        related_name='rentals',
    )
    item_name = models.CharField(max_length=200)
    item_sku = models.CharField(max_length=64, blank=True, default='')
    checked_out_at = models.DateTimeField(auto_now_add=True)
    returned_at = models.DateTimeField(null=True, blank=True)
    status = models.CharField(
        max_length=16,
        choices=RentalStatus.choices,
        default=RentalStatus.OUT,
    )
    damage_fee = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    deposit = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'rental_rental'
        ordering = ['-checked_out_at']

    def __str__(self):
        return f'Rental #{self.pk} — {self.item_name} ({self.status})'
