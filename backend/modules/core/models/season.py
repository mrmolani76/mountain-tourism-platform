"""Season (tenant DB). Winter / summer / closed periods."""

from django.db import models


class SeasonKind(models.TextChoices):
    WINTER = 'winter', 'Winter'
    SUMMER = 'summer', 'Summer'
    CLOSED = 'closed', 'Closed'


class Season(models.Model):
    kind = models.CharField(max_length=16, choices=SeasonKind.choices)
    starts_on = models.DateField()
    ends_on = models.DateField()
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_season'
        ordering = ['starts_on']

    def __str__(self):
        return f'{self.kind} {self.starts_on}..{self.ends_on}'
