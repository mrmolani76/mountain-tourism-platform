"""DRF permissions for modules.pos (tenant DB)."""

from modules.pos.permissions.pos import IsPosCashier, IsPosStaff

__all__ = ['IsPosStaff', 'IsPosCashier']
