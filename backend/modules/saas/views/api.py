"""
DRF ViewSets / APIViews for central SaaS host.

Tenant CRUD here = registry rows only (physical DB setup is separate).
"""

from django.middleware.csrf import get_token
from rest_framework import status, viewsets
from rest_framework.decorators import action
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView

from modules.saas import services
from modules.saas.auth import SaasSessionAuthentication
from modules.saas.models import Tenant
from modules.saas.permissions import IsSaasUser
from modules.saas.serializers import (
    SaasLoginSerializer,
    SaasUserSerializer,
    TenantCreateSerializer,
    TenantSerializer,
)


class SaasLoginView(APIView):
    authentication_classes = []
    permission_classes = [AllowAny]

    def post(self, request):
        serializer = SaasLoginSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        user = services.authenticate_saas_user(
            email=serializer.validated_data['email'],
            password=serializer.validated_data['password'],
        )
        request.session['saas_user_id'] = user.id
        get_token(request)
        return Response(SaasUserSerializer(user).data)


class SaasLogoutView(APIView):
    authentication_classes = [SaasSessionAuthentication]
    permission_classes = [AllowAny]

    def post(self, request):
        request.session.flush()
        return Response({'detail': 'Logged out.'})


class SaasMeView(APIView):
    authentication_classes = [SaasSessionAuthentication]
    permission_classes = [IsAuthenticated, IsSaasUser]

    def get(self, request):
        return Response(SaasUserSerializer(request.user).data)


class TenantViewSet(viewsets.ModelViewSet):
    """
    Central tenant registry.

    create → register Tenant+Domain (no physical DB).
    partial_update is_active → activate/deactivate.
    """

    authentication_classes = [SaasSessionAuthentication]
    permission_classes = [IsAuthenticated, IsSaasUser]
    queryset = Tenant.objects.prefetch_related('domains').all()
    http_method_names = ['get', 'post', 'patch', 'head', 'options']

    def get_serializer_class(self):
        if self.action == 'create':
            return TenantCreateSerializer
        return TenantSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        tenant = services.register_complex(
            name=serializer.validated_data['name'],
            slug=serializer.validated_data['slug'],
            base_domain=serializer.validated_data.get(
                'base_domain', 'local.mountain.local'
            ),
        )
        out = TenantSerializer(tenant)
        return Response(out.data, status=status.HTTP_201_CREATED)

    def partial_update(self, request, *args, **kwargs):
        tenant = self.get_object()
        if 'is_active' in request.data:
            tenant = services.set_tenant_active(
                tenant, is_active=bool(request.data['is_active'])
            )
            return Response(TenantSerializer(tenant).data)
        return super().partial_update(request, *args, **kwargs)

    @action(detail=True, methods=['post'], url_path='activate')
    def activate(self, request, pk=None):
        tenant = services.set_tenant_active(self.get_object(), is_active=True)
        return Response(TenantSerializer(tenant).data)

    @action(detail=True, methods=['post'], url_path='deactivate')
    def deactivate(self, request, pk=None):
        tenant = services.set_tenant_active(self.get_object(), is_active=False)
        return Response(TenantSerializer(tenant).data)
