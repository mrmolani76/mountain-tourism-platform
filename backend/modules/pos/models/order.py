"""Order — one order at an outlet (Module D — POS)."""

from django.db import models


class OrderStatus(models.TextChoices):
    OPEN = 'open', 'Open'
    PAID = 'paid', 'Paid'
    VOIDED = 'voided', 'Voided'


class Order(models.Model):
    """One order at an outlet, taken by a cashier/front desk."""

    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.PROTECT,
        related_name='orders',
    )
    staff = models.ForeignKey(
        'core.Staff',
        on_delete=models.PROTECT,
        related_name='pos_orders',
    )
    folio = models.ForeignKey(
        'folio.Folio',
        on_delete=models.SET_NULL,
        null=True, blank=True,
        related_name='pos_orders',
        help_text='Optional folio to charge this order to.',
    )
    status = models.CharField(
        max_length=16,
        choices=OrderStatus.choices,
        default=OrderStatus.OPEN,
    )
    total_amount = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'pos_order'
        ordering = ['-created_at']

    def __str__(self):
        return f'Order #{self.pk} — {self.outlet.name} ({self.status})'

    @property
    def is_open(self):
        return self.status == self.OrderStatus.OPEN

    @property
    def is_voided(self):
        return self.status == self.OrderStatus.VOIDED
