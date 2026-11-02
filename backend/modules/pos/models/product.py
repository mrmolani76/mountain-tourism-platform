"""Product — item sold at an outlet (Module D — POS)."""

from django.db import models


class Product(models.Model):
    """One product available at an outlet."""

    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.PROTECT,
        related_name='products',
    )
    category = models.ForeignKey(
        'pos.ProductCategory',
        on_delete=models.PROTECT,
        related_name='products',
    )
    name = models.CharField(max_length=200)
    sku = models.CharField(max_length=64, blank=True, default='')
    price = models.DecimalField(max_digits=10, decimal_places=2)
    tax_rate = models.DecimalField(max_digits=6, decimal_places=4, default=0)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'pos_product'
        ordering = ['outlet', 'name']

    def __str__(self):
        return f'{self.outlet.name} — {self.name} ({self.price})'
