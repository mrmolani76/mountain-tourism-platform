from django.apps import AppConfig


class RentalConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'modules.rental'
    label = 'rental'
    verbose_name = 'Rental'
