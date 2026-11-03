"""Hotel ViewSets — rooms, stays, board plans (Module B)."""

from rest_framework import status, viewsets
from rest_framework.decorators import action
from rest_framework.exceptions import ValidationError
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response

from modules.core.auth import StaffSessionAuthentication
from modules.core.permissions import IsTenantStaff
from modules.hotel.permissions import IsHotelManager, IsHotelStaff
from modules.hotel.serializers import (
    BoardPlanSerializer,
    RoomSerializer,
    RoomTypeSerializer,
    StayCreateSerializer,
    StaySerializer,
)
from modules.hotel.services import check_in_stay, check_out_stay
from tenancy.context import get_current_tenant


class BoardPlanViewSet(viewsets.ModelViewSet):
    """Board plan CRUD (owner/manager)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelStaff]
    serializer_class = BoardPlanSerializer

    def get_queryset(self):
        from modules.hotel.models import BoardPlan
        return BoardPlan.objects.all()

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelManager]
        return super().get_permissions()


class RoomTypeViewSet(viewsets.ModelViewSet):
    """Room type CRUD (owner/manager)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelStaff]
    serializer_class = RoomTypeSerializer

    def get_queryset(self):
        from modules.hotel.models import RoomType
        return RoomType.objects.all()

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelManager]
        return super().get_permissions()


class RoomViewSet(viewsets.ModelViewSet):
    """Room CRUD (owner/manager)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelStaff]
    serializer_class = RoomSerializer

    def get_queryset(self):
        from modules.hotel.models import Room
        return Room.objects.all()

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelManager]
        return super().get_permissions()


class StayViewSet(viewsets.ModelViewSet):
    """Stay CRUD. Check-in/check-out via custom actions."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelStaff]
    serializer_class = StaySerializer

    def get_queryset(self):
        from modules.hotel.models import Stay
        return Stay.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return StayCreateSerializer
        return StaySerializer

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsHotelManager]
        return super().get_permissions()

    @action(detail=True, methods=['post'], url_path='check-in',
            permission_classes=[IsAuthenticated, IsTenantStaff, IsHotelManager])
    def check_in(self, request, pk=None):
        stay = self.get_object()
        try:
            stay = check_in_stay(stay=stay, actor=request.user)
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(StaySerializer(stay).data)

    @action(detail=True, methods=['post'], url_path='check-out',
            permission_classes=[IsAuthenticated, IsTenantStaff, IsHotelManager])
    def check_out(self, request, pk=None):
        stay = self.get_object()
        try:
            stay = check_out_stay(stay=stay, actor=request.user)
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(StaySerializer(stay).data)
