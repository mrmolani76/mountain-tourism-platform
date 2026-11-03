"""Weather ViewSets — limits and warnings (Module H)."""

from rest_framework import viewsets
from rest_framework.permissions import IsAuthenticated

from modules.core.auth import StaffSessionAuthentication
from modules.core.permissions import IsTenantStaff
from modules.weather.permissions import IsWeatherStaff
from modules.weather.serializers import WeatherLimitSerializer, WeatherWarningSerializer
from tenancy.context import get_current_tenant


class WeatherLimitViewSet(viewsets.ModelViewSet):
    """Weather limit CRUD."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsWeatherStaff]
    serializer_class = WeatherLimitSerializer

    def get_queryset(self):
        from modules.weather.models import WeatherLimit
        return WeatherLimit.objects.all()


class WeatherWarningViewSet(viewsets.ModelViewSet):
    """Weather warning CRUD."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsWeatherStaff]
    serializer_class = WeatherWarningSerializer

    def get_queryset(self):
        from modules.weather.models import WeatherWarning
        return WeatherWarning.objects.all()
