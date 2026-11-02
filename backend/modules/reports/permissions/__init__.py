"""DRF permissions for modules.reports (tenant DB)."""

from modules.reports.permissions.reports import IsReportsStaff

__all__ = ['IsReportsStaff']
