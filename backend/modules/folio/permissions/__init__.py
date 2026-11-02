"""DRF permissions for modules.folio (tenant DB)."""

from modules.folio.permissions.folio import (
    IsFolioManager,
    IsFolioStaff,
)

__all__ = ['IsFolioManager', 'IsFolioStaff']
