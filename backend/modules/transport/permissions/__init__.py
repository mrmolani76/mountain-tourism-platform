"""DRF permissions for modules.transport (tenant DB)."""

from modules.transport.permissions.transport import IsTransportStaff

__all__ = ['IsTransportStaff']
