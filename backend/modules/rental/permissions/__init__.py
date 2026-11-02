"""DRF permissions for modules.rental (tenant DB)."""

from modules.rental.permissions.rental import IsRentalStaff

__all__ = ['IsRentalStaff']
