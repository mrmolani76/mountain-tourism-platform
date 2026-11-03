"""Rental ViewSets — equipment rental (Module E)."""

from rest_framework import viewsets
from rest_framework.permissions import IsAuthenticated

from modules.core.auth import StaffSessionAuthentication
from modules.core.permissions import IsTenantStaff
from modules.rental.permissions import IsRentalStaff
from modules.rental.serializers import RentalSerializer
from tenancy.context import get_current_tenant


class RentalViewSet(viewsets.ModelViewSet):
    """Rental CRUD — rent out, return, damage fees."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsRentalStaff]
    serializer_class = RentalSerializer

    def get_queryset(self):
        from modules.rental.models import Rental
        return Rental.objects.all()
