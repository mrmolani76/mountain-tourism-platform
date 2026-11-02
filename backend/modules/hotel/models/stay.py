"""Stay — one visit in a room/chalet/hut bed (Module B)."""

from django.db import models


class StayStatus(models.TextChoices):
    RESERVED = 'reserved', 'Reserved'
    CHECKED_IN = 'checked_in', 'Checked In'
    CHECKED_OUT = 'checked_out', 'Checked Out'
    CANCELLED = 'cancelled', 'Cancelled'


class Stay(models.Model):
    """One visit in a room, chalet, or hut bed. Links to folio for billing."""

    room = models.ForeignKey(
        'hotel.Room',
        on_delete=models.PROTECT,
        related_name='stays',
    )
    guest_name = models.CharField(max_length=200)
    check_in = models.DateField()
    check_out = models.DateField()
    status = models.CharField(
        max_length=16,
        choices=StayStatus.choices,
        default=StayStatus.RESERVED,
    )
    board_plan = models.ForeignKey(
        'hotel.BoardPlan',
        on_delete=models.PROTECT,
        related_name='stays',
    )
    folio = models.ForeignKey(
        'folio.Folio',
        on_delete=models.SET_NULL,
        null=True, blank=True,
        related_name='stays',
        help_text='Linked folio for billing charges/payments.',
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'hotel_stay'
        ordering = ['-check_in']

    def __str__(self):
        return f'Stay #{self.pk} — {self.guest_name} ({self.check_in}..{self.check_out})'

    @property
    def nights(self):
        return (self.check_out - self.check_in).days
