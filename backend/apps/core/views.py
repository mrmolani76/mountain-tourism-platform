"""
Core API views.
Health, auth (login/me/password-reset), staff, tenants.
"""
from django.contrib.auth.hashers import check_password
from django.middleware.csrf import get_token
from rest_framework.decorators import api_view, authentication_classes, permission_classes
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.reverse import reverse
from rest_framework import status

from .auth import StaffSessionAuthentication
from .models import Staff, StaffOutletAccess, Tenant
from .serializers import (
    LoginSerializer, MeSerializer, PasswordResetSerializer,
    StaffSerializer, StaffOutletAccessSerializer, TenantSerializer,
)


def _current_tenant(request):
    user = getattr(request, 'user', None)
    return getattr(user, 'tenant', None)


@api_view(['GET'])
@permission_classes([AllowAny])
def health(request):
    """API health check."""
    return Response({'status': 'ok', 'service': 'mountain-tourism-api'})


@api_view(['POST'])
@authentication_classes([])
@permission_classes([AllowAny])
def login(request):
    """Email/password login. Sets staff_id in session."""
    serializer = LoginSerializer(data=request.data)
    serializer.is_valid(raise_exception=True)
    email = serializer.validated_data['email']
    password = serializer.validated_data['password']
    try:
        staff = Staff.all_objects.get(email=email)
    except Staff.DoesNotExist:
        return Response({'detail': 'Invalid credentials.'},
                        status=status.HTTP_401_UNAUTHORIZED)
    if not check_password(password, staff.password):
        return Response({'detail': 'Invalid credentials.'},
                        status=status.HTTP_401_UNAUTHORIZED)
    if not staff.is_active:
        return Response({'detail': 'Staff inactive.'},
                        status=status.HTTP_403_FORBIDDEN)
    if not staff.tenant.is_active:
        return Response({'detail': 'Tenant inactive.'},
                        status=status.HTTP_403_FORBIDDEN)
    request.session['staff_id'] = staff.id
    staff.last_login = None  # keep field, no auto-update in MVP
    staff.save(update_fields=['last_login'])
    get_token(request)  # ensure CSRF token cookie set
    return Response(MeSerializer(staff).data)


@api_view(['POST'])
@permission_classes([AllowAny])
def logout(request):
    """Clear the session."""
    request.session.flush()
    return Response({'detail': 'Logged out.'})


@api_view(['GET'])
@permission_classes([IsAuthenticated])
def me(request):
    """Current staff identity, role, tenant, outlets."""
    return Response(MeSerializer(request.user).data)


@api_view(['GET', 'POST'])
@permission_classes([IsAuthenticated])
def staff_list(request):
    """List tenant's staff. Tenant set from request.user (never from client)."""
    if request.method == 'GET':
        staff = Staff.objects.all()
        return Response(StaffSerializer(staff, many=True).data)
    serializer = StaffSerializer(data=request.data, context={'tenant': request.user.tenant})
    if serializer.is_valid():
        serializer.save()
        return Response(serializer.data, status=status.HTTP_201_CREATED)
    return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


@api_view(['GET', 'PATCH', 'DELETE'])
@permission_classes([IsAuthenticated])
def staff_detail(request, pk):
    """Detail for one staff member in current tenant."""
    try:
        staff = Staff.objects.get(pk=pk)
    except Staff.DoesNotExist:
        return Response(status=status.HTTP_404_NOT_FOUND)
    if request.method == 'GET':
        return Response(StaffSerializer(staff).data)
    if request.method == 'PATCH':
        serializer = StaffSerializer(staff, data=request.data, partial=True)
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
    staff.delete()
    return Response(status=status.HTTP_204_NO_CONTENT)


@api_view(['POST'])
@permission_classes([IsAuthenticated])
def reset_password(request):
    """Owner/manager resets a staff password (no email in MVP)."""
    if not request.user.can_reset_password:
        return Response({'detail': 'Only owner/manager can reset passwords.'},
                        status=status.HTTP_403_FORBIDDEN)
    serializer = PasswordResetSerializer(data=request.data)
    serializer.is_valid(raise_exception=True)
    try:
        # all_objects: bypass tenant filter; cross-tenant check below enforces isolation.
        target = Staff.all_objects.get(pk=serializer.validated_data['staff_id'])
    except Staff.DoesNotExist:
        return Response({'detail': 'Staff not found.'}, status=status.HTTP_404_NOT_FOUND)
    if target.tenant_id != request.user.tenant_id:
        return Response({'detail': 'Cross-tenant not allowed.'},
                        status=status.HTTP_403_FORBIDDEN)
    from django.contrib.auth.hashers import make_password
    target.password = make_password(serializer.validated_data['new_password'])
    target.save(update_fields=['password'])
    return Response({'detail': 'Password reset.'})


@api_view(['GET', 'POST'])
@permission_classes([IsAuthenticated])
def outlet_access_list(request):
    """Staff ↔ outlet access mappings (tenant-scoped)."""
    if request.method == 'GET':
        accesses = StaffOutletAccess.objects.all()
        return Response(StaffOutletAccessSerializer(accesses, many=True).data)
    serializer = StaffOutletAccessSerializer(data=request.data)
    if serializer.is_valid():
        serializer.save()
        return Response(serializer.data, status=status.HTTP_201_CREATED)
    return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


@api_view(['GET', 'POST'])
@permission_classes([IsAuthenticated])
def tenant_list(request):
    """List/create tenants (super admin surface in item 07)."""
    if request.method == 'GET':
        return Response([
            {'id': t.id, 'name': t.name, 'is_active': t.is_active}
            for t in Tenant.objects.all()
        ])
    serializer = TenantSerializer(data=request.data)
    if serializer.is_valid():
        serializer.save()
        return Response(serializer.data, status=status.HTTP_201_CREATED)
    return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


@api_view(['GET'])
@permission_classes([IsAuthenticated])
def api_root(request):
    """API root listing available namespaces."""
    return Response({
        'health': reverse('health', request=request),
        'login': reverse('login', request=request),
        'me': reverse('me', request=request),
        'logout': reverse('logout', request=request),
        'staff': reverse('staff-list', request=request),
        'outlet-access': reverse('outlet-access-list', request=request),
        'tenants': reverse('tenant-list', request=request),
        'note': 'Module A endpoints (tenants, staff, buildings, outlets, seasons) added in items 04-07.',
    })
