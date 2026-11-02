"""Order line — one line item in an order (Module D — POS)."""

from django.db import models


class OrderLine(models.Model):
    """One product line in an order."""

    order = models.ForeignKey(
        'pos.Order',
        on_delete=models.CASCADE,
        related_name='lines',
    )
    product = models.ForeignKey(
        'pos.Product',
        on_delete=models.PROTECT,
        related_name='order_lines',
    )
    quantity = models.PositiveIntegerField(default=1)
    unit_price = models.DecimalField(max_digits=10, decimal_places=2)
    tax_amount = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Void fields (Rule 5: append-only, void only)
    is_voided = models.BooleanField(default=False)
    voided_at = models.DateTimeField(null=True, blank=True)
    voided_reason = models.CharField(max_length=200, blank=True, default='')
    voided_by = models.IntegerField(null=True, blank=True)

    objects = models.Manager()

    class Meta:
        db_table = 'pos_order_line'
        ordering = ['-created_at']

    def __str__(self):
        return f'OrderLine #{self.pk} — {self.product.name} x{self.quantity}'

    @property
    def line_total(self):
        return self.unit_price * self.quantity + self.tax_amount
