"""DRF serializers for modules.transport (tenant DB)."""

from modules.transport.serializers.trip import TripSerializer
from modules.transport.serializers.vehicle import VehicleSerializer
from modules.transport.serializers.driver import DriverSerializer

__all__ = ['TripSerializer', 'VehicleSerializer', 'DriverSerializer']
