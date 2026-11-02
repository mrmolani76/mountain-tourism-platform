"""Folio — bill of a stay (Module C). Lives in the tenant DB."""

from django.db import models


class FolioStatus(models.TextChoices):
    OPEN = 'open', 'Open'
    CLOSED = 'closed', 'Closed'
    VOIDED = 'voided', 'Voided'


class Folio(models.Model):
    """One folio per guest/stay. Total = sum of charges minus sum of payments."""

    guest_name = models.CharField(max_length=200)
    stay_reference = models.CharField(
        max_length=100, blank=True, default='',
        help_text='Optional link to hotel stay / booking reference.',
    )
    status = models.CharField(
        max_length=16,
        choices=FolioStatus.choices,
        default=FolioStatus.OPEN,
    )
    currency = models.CharField(max_length=8, default='EUR')
    total_amount = models.DecimalField(
        max_digits=12, decimal_places=2, default=0,
        help_text='Computed: sum(charges) - sum(payments).',
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Void fields (Rule 5: append-only, void only)
    voided_at = models.DateTimeField(null=True, blank=True)
    voided_reason = models.CharField(max_length=200, blank=True, default='')
    voided_by = models.IntegerField(null=True, blank=True, help_text='Staff pk who voided.')

    objects = models.Manager()

    class Meta:
        db_table = 'folio_folio'
        ordering = ['-created_at']

    def __str__(self):
        return f'Folio #{self.pk} — {self.guest_name} ({self.status})'

    @property
    def is_open(self):
        return self.status == self.FolioStatus.OPEN

    @property
    def is_closed(self):
        return self.status == self.FolioStatus.CLOSED

    @property
    def is_voided(self):
        return self.status == self.FolioStatus.VOIDED

    def compute_total(self):
        """Recompute total from charges and payments on this folio."""
        from modules.folio.models.charge import Charge
        from modules.folio.models.payment import Payment
        charge_total = Charge.objects.filter(
            folio=self, is_voided=False
        ).aggregate(
            total=models.Sum('amount') + models.Sum('tax_amount')
        )['total'] or 0
        payment_total = Payment.objects.filter(
            folio=self, is_voided=False
        ).aggregate(total=models.Sum('amount'))['total'] or 0
        return charge_total - payment_total
