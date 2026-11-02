"""DRF permissions for modules.hotel (tenant DB)."""

from modules.hotel.permissions.hotel import IsHotelStaff, IsHotelManager

__all__ = ['IsHotelStaff', 'IsHotelManager']
