"""Payment — cash or card recorded by staff (Module C). Append-only; void only."""

from django.db import models


class PaymentMethod(models.TextChoices):
    CASH = 'cash', 'Cash'
    CARD = 'card', 'Card'


class Payment(models.Model):
    """One payment against a folio. Several per folio. Never edited/deleted."""

    folio = models.ForeignKey(
        'folio.Folio',
        on_delete=models.PROTECT,
        related_name='payments',
    )
    method = models.CharField(max_length=16, choices=PaymentMethod.choices)
    amount = models.DecimalField(max_digits=12, decimal_places=2)
    reference = models.CharField(
        max_length=100, blank=True, default='',
        help_text='Card reference, receipt number, etc.',
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
        db_table = 'folio_payment'
        ordering = ['-created_at']

    def __str__(self):
        return f'Payment #{self.pk} — {self.method} ({self.amount})'

    def void(self, *, reason: str, voided_by: int):
        """Mark this payment as voided (append-only: never delete)."""
        if self.is_voided:
            from django.core.exceptions import ValidationError
            raise ValidationError('Payment already voided.')
        self.is_voided = True
        self.voided_at = models.DateTimeField.auto_now  # placeholder, set in save
        self.voided_reason = reason
        self.voided_by = voided_by
        self.save()
