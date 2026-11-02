from django.apps import AppConfig


class WeatherConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'modules.weather'
    label = 'weather'
    verbose_name = 'Weather'
