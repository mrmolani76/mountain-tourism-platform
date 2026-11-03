"""Order serializers."""

from rest_framework import serializers

from modules.pos.models import Order


class OrderSerializer(serializers.ModelSerializer):
    outlet_name = serializers.CharField(source='outlet.name', read_only=True)
    staff_email = serializers.CharField(source='staff.email', read_only=True)

    class Meta:
        model = Order
        fields = [
            'id', 'outlet', 'outlet_name', 'staff', 'staff_email', 'folio',
            'status', 'total_amount', 'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'outlet_name', 'staff_email', 'total_amount',
                    'created_at', 'updated_at']


class OrderCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Order
        fields = ['outlet', 'staff', 'folio']
