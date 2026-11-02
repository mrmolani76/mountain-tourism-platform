"""DRF views for modules.hotel (tenant DB)."""

from modules.hotel.views.hotel import BoardPlanViewSet, RoomTypeViewSet, RoomViewSet, StayViewSet

__all__ = ['BoardPlanViewSet', 'RoomTypeViewSet', 'RoomViewSet', 'StayViewSet']
