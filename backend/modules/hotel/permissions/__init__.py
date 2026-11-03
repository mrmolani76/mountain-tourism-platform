"""DRF permissions for modules.hotel (tenant DB)."""

from modules.hotel.permissions.hotel import IsHotelManager, IsHotelStaff

__all__ = ['IsHotelStaff', 'IsHotelManager']
