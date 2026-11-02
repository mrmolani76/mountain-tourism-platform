"""Room — individual room/bed in a building (Module B)."""

from django.db import models


class Room(models.Model):
    """One physical room or bed. Linked to a building and room type."""

    building = models.ForeignKey(
        'core.Building',
        on_delete=models.PROTECT,
        related_name='rooms',
    )
    room_type = models.ForeignKey(
        'hotel.RoomType',
        on_delete=models.PROTECT,
        related_name='rooms',
    )
    number = models.CharField(max_length=20, help_text='Room number or bed identifier.')
    floor = models.PositiveIntegerField(null=True, blank=True)
    is_active = models.BooleanField(default=True)
    is_available = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'hotel_room'
        ordering = ['building', 'number']
        unique_together = [['building', 'number']]

    def __str__(self):
        return f'{self.building.name} — {self.number}'
