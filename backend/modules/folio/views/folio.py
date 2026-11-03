"""Folio ViewSets — money domain (append-only, void only)."""

from rest_framework import status, viewsets
from rest_framework.decorators import action
from rest_framework.exceptions import PermissionDenied, ValidationError
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response

from modules.core.auth import StaffSessionAuthentication
from modules.core.models import Staff
from modules.core.permissions import IsTenantStaff
from modules.folio.permissions import IsFolioManager, IsFolioStaff
from modules.folio.serializers import (
    ChargeCreateSerializer,
    ChargeSerializer,
    ChargeVoidSerializer,
    FolioCloseSerializer,
    FolioCreateSerializer,
    FolioSerializer,
    PaymentCreateSerializer,
    PaymentSerializer,
    PaymentVoidSerializer,
)
from modules.folio.services import close_folio, open_folio, post_charge, record_payment, void_charge, void_payment
from tenancy.context import get_current_tenant


class FolioViewSet(viewsets.ModelViewSet):
    """Folio CRUD. Open/close folios; read charges+payments via nested endpoints."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsFolioStaff]
    queryset = None  # set per-request in get_queryset
    serializer_class = FolioSerializer

    def get_queryset(self):
        tenant = get_current_tenant()
        if tenant is None:
            from modules.folio.models import Folio
            return Folio.objects.none()
        from modules.folio.models import Folio
        return Folio.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return FolioCreateSerializer
        if self.action == 'close':
            return FolioCloseSerializer
        return FolioSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        folio = open_folio(
            guest_name=serializer.validated_data['guest_name'],
            stay_reference=serializer.validated_data.get('stay_reference', ''),
            currency=serializer.validated_data.get('currency', 'EUR'),
        )
        return Response(FolioSerializer(folio).data, status=status.HTTP_201_CREATED)

    @action(detail=True, methods=['post'], url_path='close',
            permission_classes=[IsAuthenticated, IsTenantStaff, IsFolioManager])
    def close(self, request, pk=None):
        folio = self.get_object()
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        try:
            folio = close_folio(
                folio=folio,
                actor=request.user,
                confirm_zero_balance=serializer.validated_data.get(
                    'confirm_zero_balance', False
                ),
            )
        except (ValidationError, PermissionDenied) as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(FolioSerializer(folio).data)


class ChargeViewSet(viewsets.ModelViewSet):
    """Charge CRUD. Create = post charge. POST /{id}/void/ = void (Rule 5)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsFolioStaff]
    serializer_class = ChargeSerializer

    def get_queryset(self):
        from modules.folio.models import Charge
        return Charge.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return ChargeCreateSerializer
        if self.action == 'void':
            return ChargeVoidSerializer
        return ChargeSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        folio = serializer.validated_data['folio']
        try:
            charge = post_charge(
                folio=folio,
                outlet=serializer.validated_data['outlet'],
                description=serializer.validated_data['description'],
                amount=serializer.validated_data['amount'],
                tax_amount=serializer.validated_data.get('tax_amount', 0),
                tax_rate=serializer.validated_data.get('tax_rate', 0),
                source=serializer.validated_data['source'],
                actor=request.user,
            )
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(ChargeSerializer(charge).data, status=status.HTTP_201_CREATED)

    @action(detail=True, methods=['post'], url_path='void',
            permission_classes=[IsAuthenticated, IsTenantStaff, IsFolioManager])
    def void(self, request, pk=None):
        charge = self.get_object()
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        try:
            charge = void_charge(
                charge=charge,
                reason=serializer.validated_data['reason'],
                voided_by=serializer.validated_data['voided_by'],
            )
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(ChargeSerializer(charge).data)

    def update(self, request, *args, **kwargs):
        return Response({'detail': 'Charges are append-only. Use void instead.'}, status=405)

    def partial_update(self, request, *args, **kwargs):
        return Response({'detail': 'Charges are append-only. Use void instead.'}, status=405)

    def destroy(self, request, *args, **kwargs):
        return Response({'detail': 'Charges are append-only. Use void instead.'}, status=405)


class PaymentViewSet(viewsets.ModelViewSet):
    """Payment CRUD. Create = record payment. POST /{id}/void/ = void (Rule 5)."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsFolioStaff]
    serializer_class = PaymentSerializer

    def get_queryset(self):
        from modules.folio.models import Payment
        return Payment.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return PaymentCreateSerializer
        if self.action == 'void':
            return PaymentVoidSerializer
        return PaymentSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        folio = serializer.validated_data['folio']
        try:
            payment = record_payment(
                folio=folio,
                method=serializer.validated_data['method'],
                amount=serializer.validated_data['amount'],
                reference=serializer.validated_data.get('reference', ''),
                actor=request.user,
            )
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(PaymentSerializer(payment).data, status=status.HTTP_201_CREATED)

    @action(detail=True, methods=['post'], url_path='void',
            permission_classes=[IsAuthenticated, IsTenantStaff, IsFolioManager])
    def void(self, request, pk=None):
        payment = self.get_object()
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        try:
            payment = void_payment(
                payment=payment,
                reason=serializer.validated_data['reason'],
                voided_by=serializer.validated_data['voided_by'],
            )
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(PaymentSerializer(payment).data)

    def update(self, request, *args, **kwargs):
        return Response({'detail': 'Payments are append-only. Use void instead.'}, status=405)

    def partial_update(self, request, *args, **kwargs):
        return Response({'detail': 'Payments are append-only. Use void instead.'}, status=405)

    def destroy(self, request, *args, **kwargs):
        return Response({'detail': 'Payments are append-only. Use void instead.'}, status=405)
