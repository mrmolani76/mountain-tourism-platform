"""Room type — category of room (single, double, suite, dorm, etc.) (Module B)."""

from django.db import models


class RoomType(models.Model):
    """Category of room/bed available in the complex."""

    name = models.CharField(max_length=100)
    code = models.CharField(max_length=32, unique=True)
    description = models.CharField(max_length=255, blank=True, default='')
    capacity = models.PositiveIntegerField(default=1, help_text='Number of guests this room type sleeps.')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'hotel_room_type'
        ordering = ['code']

    def __str__(self):
        return f'{self.code} — {self.name} ({self.capacity} guests)'
