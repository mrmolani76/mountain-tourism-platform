"""
DRF APIViews + ViewSets for tenant host (/api/).
"""

from django.middleware.csrf import get_token
from rest_framework import viewsets
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.reverse import reverse
from rest_framework.views import APIView

from modules.core.auth import StaffSessionAuthentication
from modules.core.models import Staff, StaffOutletAccess
from modules.core.permissions import CanResetStaffPassword, IsTenantStaff
from modules.core.serializers import (
    LoginSerializer,
    MeSerializer,
    PasswordResetSerializer,
    StaffOutletAccessSerializer,
    StaffSerializer,
)
from modules.core.services import authenticate_staff, reset_staff_password
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
        return Response({
            'health': reverse('health', request=request),
            'login': reverse('login', request=request),
            'me': reverse('me', request=request),
            'logout': reverse('logout', request=request),
            'staff': reverse('staff-list', request=request),
            'outlet-access': reverse('outlet-access-list', request=request),
        })


class LoginView(APIView):
    authentication_classes = []
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = LoginSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        staff = authenticate_staff(
            email=serializer.validated_data['email'],
            password=serializer.validated_data['password'],
        )
        request.session['staff_id'] = staff.id
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
    """Staff CRUD on the active tenant database."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff]
    serializer_class = StaffSerializer
    queryset = Staff.objects.all()
    http_method_names = ['get', 'post', 'patch', 'delete', 'head', 'options']


class StaffOutletAccessViewSet(viewsets.ModelViewSet):
    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff]
    serializer_class = StaffOutletAccessSerializer
    queryset = StaffOutletAccess.objects.all()
    http_method_names = ['get', 'post', 'delete', 'head', 'options']
