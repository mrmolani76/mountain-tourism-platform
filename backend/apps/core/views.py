"""
Core API views.
Health endpoint (no auth) + tenant-scoped staff/tenant stubs (item 04 test).
"""
from rest_framework.decorators import api_view, permission_classes
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.reverse import reverse
from rest_framework import status

from .models import Staff, Tenant
from .serializers import StaffSerializer, TenantSerializer


@api_view(['GET'])
@permission_classes([AllowAny])
def health(request):
    """API health check."""
    return Response({'status': 'ok', 'service': 'mountain-tourism-api'})


@api_view(['GET'])
@permission_classes([IsAuthenticated])
def api_root(request):
    """API root listing available namespaces."""
    return Response({
        'health': reverse('health', request=request),
        'staff': reverse('staff-list', request=request),
        'tenants': reverse('tenant-list', request=request),
        'note': 'Module A endpoints (tenants, staff, buildings, outlets, seasons) added in items 04-07.',
    })


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
