"""Product category — grouping of products (Module D — POS)."""

from django.db import models


class ProductCategory(models.Model):
    """Category of products sold at an outlet."""

    name = models.CharField(max_length=100)
    code = models.CharField(max_length=32, unique=True)
    description = models.CharField(max_length=255, blank=True, default='')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'pos_product_category'
        ordering = ['code']

    def __str__(self):
        return f'{self.code} — {self.name}'
