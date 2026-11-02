"""Transport models (Module G)."""

from modules.transport.models.trip import Trip, TripStatus
from modules.transport.models.vehicle import Vehicle, VehicleType
from modules.transport.models.driver import Driver

__all__ = ['Trip', 'TripStatus', 'Vehicle', 'VehicleType', 'Driver']
