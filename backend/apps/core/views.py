"""
Core API views.
Health endpoint (no auth) + API root stub (auth required).
"""
from rest_framework.decorators import api_view, permission_classes
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.reverse import reverse


@api_view(['GET'])
@permission_classes([AllowAny])
def health(request):
    """API health check."""
    return Response({'status': 'ok', 'service': 'mountain-tourism-api'})


@api_view(['GET'])
@permission_classes([IsAuthenticated])
def api_root(request):
    """API root listing available namespaces (stub for Module A endpoints)."""
    return Response({
        'health': reverse('health', request=request),
        'note': 'Module A endpoints (tenants, staff, buildings, outlets, seasons) added in items 04-07.',
    })
