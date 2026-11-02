"""Transport ViewSets — trips, vehicles, drivers (Module G)."""

from rest_framework import viewsets
from rest_framework.permissions import IsAuthenticated

from modules.core.permissions import IsTenantStaff
from modules.transport.permissions import IsTransportStaff
from modules.transport.serializers import DriverSerializer, TripSerializer, VehicleSerializer
from tenancy.context import get_current_tenant


class TripViewSet(viewsets.ModelViewSet):
    """Trip CRUD — one run of transport."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsTransportStaff]
    serializer_class = TripSerializer

    def get_queryset(self):
        from modules.transport.models import Trip
        return Trip.objects.all()


class VehicleViewSet(viewsets.ModelViewSet):
    """Vehicle CRUD."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsTransportStaff]
    serializer_class = VehicleSerializer

    def get_queryset(self):
        from modules.transport.models import Vehicle
        return Vehicle.objects.all()


class DriverViewSet(viewsets.ModelViewSet):
    """Driver CRUD."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsTransportStaff]
    serializer_class = DriverSerializer

    def get_queryset(self):
        from modules.transport.models import Driver
        return Driver.objects.all()
