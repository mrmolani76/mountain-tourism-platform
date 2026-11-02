"""Charge serializers."""

from rest_framework import serializers

from modules.folio.models import Charge


class ChargeSerializer(serializers.ModelSerializer):
    outlet_name = serializers.CharField(source='outlet.name', read_only=True)

    class Meta:
        model = Charge
        fields = [
            'id', 'folio', 'outlet', 'outlet_name', 'description',
            'amount', 'tax_amount', 'tax_rate', 'source',
            'is_voided', 'voided_at', 'voided_reason', 'voided_by',
            'created_at', 'updated_at',
        ]
        read_only_fields = [
            'id', 'outlet_name', 'is_voided', 'voided_at',
            'voided_reason', 'voided_by', 'created_at', 'updated_at',
        ]


class ChargeCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Charge
        fields = ['folio', 'outlet', 'description', 'amount', 'tax_amount', 'tax_rate', 'source']

    def validate_amount(self, value):
        if value <= 0:
            raise serializers.ValidationError('Amount must be positive.')
        return value


class ChargeVoidSerializer(serializers.Serializer):
    reason = serializers.CharField(max_length=200)
    voided_by = serializers.IntegerField()
