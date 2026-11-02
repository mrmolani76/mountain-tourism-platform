"""DRF permissions for modules.pos (tenant DB)."""

from modules.pos.permissions.pos import IsPosStaff, IsPosCashier

__all__ = ['IsPosStaff', 'IsPosCashier']
