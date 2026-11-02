"""Board plan — room only, B&B, half board, full board (Module B)."""

from django.db import models


class BoardPlan(models.Model):
    """Board plan for a stay. Determines included meals."""

    name = models.CharField(max_length=100)
    code = models.CharField(max_length=16, unique=True)
    description = models.CharField(max_length=255, blank=True, default='')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'hotel_board_plan'
        ordering = ['code']

    def __str__(self):
        return f'{self.code} — {self.name}'


class BoardPlanKind(models.TextChoices):
    ROOM_ONLY = 'room_only', 'Room Only'
    BED_AND_BREAKFAST = 'bb', 'Bed & Breakfast'
    HALF_BOARD = 'half_board', 'Half Board'
    FULL_BOARD = 'full_board', 'Full Board'
