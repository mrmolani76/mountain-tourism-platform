"""DRF permissions for modules.weather (tenant DB)."""

from modules.weather.permissions.weather import IsWeatherStaff

__all__ = ['IsWeatherStaff']
