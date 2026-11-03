"""
DRF APIViews + ViewSets for tenant host (/api/).
"""

from django.middleware.csrf import get_token
from rest_framework import viewsets
from rest_framework.exceptions import AuthenticationFailed
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.reverse import reverse
from rest_framework.views import APIView

from modules.core.auth import (
    SESSION_TENANT_ID,
    SESSION_TENANT_SLUG,
    StaffSessionAuthentication,
)
from modules.core.models import Building, Outlet, Season, Staff, StaffOutletAccess
from modules.core.permissions import CanResetStaffPassword, IsOwner, IsTenantStaff
from modules.core.serializers import (
    BuildingSerializer,
    LoginSerializer,
    MeSerializer,
    OutletSerializer,
    PasswordResetSerializer,
    SeasonSerializer,
    StaffOutletAccessSerializer,
    StaffSerializer,
    TenantSettingsSerializer,
)
from modules.core.services import authenticate_staff, get_tenant_settings, reset_staff_password
from tenancy.context import get_current_tenant


class HealthView(APIView):
    authentication_classes = []
    permission_classes = [AllowAny]

    def get(self, request):
        return Response({'status': 'ok', 'service': 'mountain-tourism-api'})


class ApiRootView(APIView):
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff]

    def get(self, request):
        tenant = get_current_tenant()
        if tenant is not None:
            body = {'tenant': tenant.slug}
        else:
            body = {}
        body.update({
            'health': reverse('health', request=request),
            'login': reverse('login', request=request),
            'me': reverse('me', request=request),
            'logout': reverse('logout', request=request),
            'settings': reverse('settings', request=request),
            'staff': reverse('staff-list', request=request),
            'outlet-access': reverse('outlet-access-list', request=request),
            'buildings': reverse('building-list', request=request),
            'outlets': reverse('outlet-list', request=request),
            'seasons': reverse('season-list', request=request),
        })
        return Response(body)


class LoginView(APIView):
    # StaffSessionAuthentication defines authenticate_header, so DRF returns 401
    # (not 403) when credentials fail. AllowAny still lets guests through.
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = LoginSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        staff = authenticate_staff(
            email=serializer.validated_data['email'],
            password=serializer.validated_data['password'],
        )
        tenant = get_current_tenant()
        if tenant is None:
            # Staff login is a tenant-host flow only (BUG-TENANT-001).
            raise AuthenticationFailed('Staff login requires a tenant host.')
        request.session['staff_id'] = staff.id
        request.session[SESSION_TENANT_ID] = tenant.pk
        request.session[SESSION_TENANT_SLUG] = tenant.slug
        get_token(request)
        return Response(
            MeSerializer(staff, context={'tenant': get_current_tenant()}).data
        )


class LogoutView(APIView):
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [AllowAny]

    def post(self, request):
        request.session.flush()
        return Response({'detail': 'Logged out.'})


class MeView(APIView):
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff]

    def get(self, request):
        return Response(
            MeSerializer(
                request.user, context={'tenant': get_current_tenant()}
            ).data
        )


class PasswordResetView(APIView):
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, CanResetStaffPassword]

    def post(self, request):
        serializer = PasswordResetSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        reset_staff_password(
            actor=request.user,
            staff_id=serializer.validated_data['staff_id'],
            new_password=serializer.validated_data['new_password'],
        )
        return Response({'detail': 'Password reset.'})


class StaffViewSet(viewsets.ModelViewSet):
    """Staff CRUD under owner administration (active tenant DB)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]
    serializer_class = StaffSerializer
    queryset = Staff.objects.all()
    http_method_names = ['get', 'post', 'patch', 'delete', 'head', 'options']


class StaffOutletAccessViewSet(viewsets.ModelViewSet):
    """Outlet access assign/unassign (owner only)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]
    serializer_class = StaffOutletAccessSerializer
    queryset = StaffOutletAccess.objects.all()
    http_method_names = ['get', 'post', 'delete', 'head', 'options']


class TenantSettingsView(APIView):
    """Singleton settings row: GET reads, PATCH partially updates (owner)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]

    def get(self, request):
        return Response(TenantSettingsSerializer(get_tenant_settings()).data)

    def patch(self, request):
        settings = get_tenant_settings()
        serializer = TenantSettingsSerializer(
            settings, data=request.data, partial=True
        )
        serializer.is_valid(raise_exception=True)
        serializer.save()
        return Response(serializer.data)


class BuildingViewSet(viewsets.ModelViewSet):
    """Building CRUD with altitude + map position (owner)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]
    serializer_class = BuildingSerializer
    queryset = Building.objects.all()
    http_method_names = ['get', 'post', 'patch', 'delete', 'head', 'options']


class OutletViewSet(viewsets.ModelViewSet):
    """Outlet CRUD under buildings (owner)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]
    serializer_class = OutletSerializer
    queryset = Outlet.objects.all()
    http_method_names = ['get', 'post', 'patch', 'delete', 'head', 'options']


class SeasonViewSet(viewsets.ModelViewSet):
    """Season CRUD with kind + dates + overlap rule (owner)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsOwner]
    serializer_class = SeasonSerializer
    queryset = Season.objects.all()
    http_method_names = ['get', 'post', 'patch', 'delete', 'head', 'options']
