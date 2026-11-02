"""POS ViewSets — products, orders, order lines (Module D)."""

from rest_framework import status, viewsets
from rest_framework.exceptions import ValidationError
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response

from modules.core.permissions import IsTenantStaff
from modules.pos.permissions import IsPosCashier, IsPosStaff
from modules.pos.serializers import (
    OrderCreateSerializer,
    OrderLineCreateSerializer,
    OrderLineSerializer,
    OrderSerializer,
    ProductCategorySerializer,
    ProductCreateSerializer,
    ProductSerializer,
)
from modules.pos.services import add_order_line, place_order
from tenancy.context import get_current_tenant


class ProductCategoryViewSet(viewsets.ModelViewSet):
    """Product category CRUD (owner/manager)."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsPosStaff]
    serializer_class = ProductCategorySerializer

    def get_queryset(self):
        from modules.pos.models import ProductCategory
        return ProductCategory.objects.all()

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        return super().get_permissions()


class ProductViewSet(viewsets.ModelViewSet):
    """Product CRUD (owner/manager)."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsPosStaff]
    serializer_class = ProductSerializer

    def get_queryset(self):
        from modules.pos.models import Product
        return Product.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return ProductCreateSerializer
        return ProductSerializer

    def get_permissions(self):
        if self.action in ('create', 'update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        return super().get_permissions()


class OrderViewSet(viewsets.ModelViewSet):
    """Order CRUD. Cashier/front desk can create orders."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsPosStaff]
    serializer_class = OrderSerializer

    def get_queryset(self):
        from modules.pos.models import Order
        return Order.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return OrderCreateSerializer
        return OrderSerializer

    def get_permissions(self):
        if self.action == 'create':
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        elif self.action in ('update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        return super().get_permissions()

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        order = place_order(
            outlet=serializer.validated_data['outlet'],
            staff=request.user,
            folio=serializer.validated_data.get('folio'),
        )
        return Response(OrderSerializer(order).data, status=status.HTTP_201_CREATED)


class OrderLineViewSet(viewsets.ModelViewSet):
    """Order line CRUD. Add lines to open orders."""

    authentication_classes = []
    permission_classes = [IsAuthenticated, IsTenantStaff, IsPosStaff]
    serializer_class = OrderLineSerializer

    def get_queryset(self):
        from modules.pos.models import OrderLine
        return OrderLine.objects.all()

    def get_serializer_class(self):
        if self.action == 'create':
            return OrderLineCreateSerializer
        return OrderLineSerializer

    def get_permissions(self):
        if self.action == 'create':
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        elif self.action in ('update', 'partial_update', 'destroy'):
            self.permission_classes = [IsAuthenticated, IsTenantStaff, IsPosCashier]
        return super().get_permissions()

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        try:
            line = add_order_line(
                order=serializer.validated_data['order'],
                product=serializer.validated_data['product'],
                quantity=serializer.validated_data['quantity'],
                unit_price=serializer.validated_data['unit_price'],
                tax_amount=serializer.validated_data.get('tax_amount', 0),
            )
        except ValidationError as exc:
            return Response({'detail': str(exc)}, status=400)
        return Response(OrderLineSerializer(line).data, status=status.HTTP_201_CREATED)
