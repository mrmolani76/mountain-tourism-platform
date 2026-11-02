"""Weather warning — raised on what it affects (Module H — weather)."""

from django.db import models


class WeatherWarning(models.Model):
    """A weather warning raised when a limit is exceeded."""

    weather_limit = models.ForeignKey(
        'weather.WeatherLimit',
        on_delete=models.PROTECT,
        related_name='warnings',
    )
    affected_area = models.CharField(max_length=200)
    description = models.CharField(max_length=255)
    current_value = models.DecimalField(max_digits=10, decimal_places=2)
    raised_at = models.DateTimeField(auto_now_add=True)
    resolved_at = models.DateTimeField(null=True, blank=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'weather_weatherwarning'
        ordering = ['-raised_at']

    def __str__(self):
        return f'Warning: {self.weather_limit.limit_type} — {self.affected_area}'
