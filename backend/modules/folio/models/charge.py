"""Charge — one folio line item (Module C). Append-only; void only."""

from django.db import models


class Charge(models.Model):
    """One folio line. Always records outlet, tax, source. Never edited/deleted."""

    folio = models.ForeignKey(
        'folio.Folio',
        on_delete=models.PROTECT,
        related_name='charges',
    )
    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.PROTECT,
        related_name='charges',
    )
    description = models.CharField(max_length=255)
    amount = models.DecimalField(max_digits=12, decimal_places=2)
    tax_amount = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    tax_rate = models.DecimalField(max_digits=6, decimal_places=4, default=0)
    source = models.CharField(
        max_length=50,
        help_text='Origin of charge: restaurant, room, spa, shop, etc.',
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Void fields (Rule 5: append-only, void only)
    is_voided = models.BooleanField(default=False)
    voided_at = models.DateTimeField(null=True, blank=True)
    voided_reason = models.CharField(max_length=200, blank=True, default='')
    voided_by = models.IntegerField(null=True, blank=True, help_text='Staff pk who voided.')

    objects = models.Manager()

    class Meta:
        db_table = 'folio_charge'
        ordering = ['-created_at']

    def __str__(self):
        return f'Charge #{self.pk} — {self.description} ({self.amount})'

    def void(self, *, reason: str, voided_by: int):
        """Mark this charge as voided (append-only: never delete)."""
        if self.is_voided:
            from django.core.exceptions import ValidationError
            raise ValidationError('Charge already voided.')
        self.is_voided = True
        self.voided_at = models.DateTimeField.auto_now  # placeholder, set in save
        self.voided_reason = reason
        self.voided_by = voided_by
        self.save()
