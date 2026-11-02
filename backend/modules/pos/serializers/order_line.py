"""OrderLine serializers."""

from rest_framework import serializers

from modules.pos.models import OrderLine


class OrderLineSerializer(serializers.ModelSerializer):
    product_name = serializers.CharField(source='product.name', read_only=True)

    class Meta:
        model = OrderLine
        fields = [
            'id', 'order', 'product', 'product_name', 'quantity',
            'unit_price', 'tax_amount', 'is_voided', 'voided_at',
            'voided_reason', 'voided_by', 'created_at', 'updated_at',
        ]
        read_only_fields = [
            'id', 'product_name', 'is_voided', 'voided_at',
            'voided_reason', 'voided_by', 'created_at', 'updated_at',
        ]


class OrderLineCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = OrderLine
        fields = ['order', 'product', 'quantity', 'unit_price', 'tax_amount']
