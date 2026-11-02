"""DRF permissions for modules.services (tenant DB)."""

from modules.services.permissions.services import IsServicesStaff

__all__ = ['IsServicesStaff']
