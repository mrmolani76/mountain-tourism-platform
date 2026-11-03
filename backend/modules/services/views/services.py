"""Services ViewSets — services and slots (Module F)."""

from rest_framework import viewsets
from rest_framework.permissions import IsAuthenticated

from modules.core.auth import StaffSessionAuthentication
from modules.core.permissions import IsTenantStaff
from modules.services.permissions import IsServicesStaff
from modules.services.serializers import ServiceSerializer, SlotSerializer
from tenancy.context import get_current_tenant


class ServiceViewSet(viewsets.ModelViewSet):
    """Service CRUD."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsServicesStaff]
    serializer_class = ServiceSerializer

    def get_queryset(self):
        from modules.services.models import Service
        return Service.objects.all()


class SlotViewSet(viewsets.ModelViewSet):
    """Slot CRUD — one time slot with capacity and staff."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsServicesStaff]
    serializer_class = SlotSerializer

    def get_queryset(self):
        from modules.services.models import Slot
        return Slot.objects.all()
