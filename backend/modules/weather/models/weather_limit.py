"""Weather limit — limit set by complex (Module H — weather)."""

from django.db import models


class WeatherLimitType(models.TextChoices):
    WIND_SPEED = 'wind_speed', 'Wind Speed'
    TEMPERATURE = 'temperature', 'Temperature'
    SNOWFALL = 'snowfall', 'Snowfall'
    VISIBILITY = 'visibility', 'Visibility'


class WeatherLimit(models.Model):
    """A weather limit set by the complex. When exceeded, warnings are raised."""

    limit_type = models.CharField(
        max_length=16,
        choices=WeatherLimitType.choices,
    )
    threshold_value = models.DecimalField(max_digits=10, decimal_places=2)
    threshold_unit = models.CharField(max_length=16, default='')
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = models.Manager()

    class Meta:
        db_table = 'weather_weatherlimit'
        ordering = ['limit_type']

    def __str__(self):
        return f'{self.limit_type} ≤ {self.threshold_value}{self.threshold_unit}'
