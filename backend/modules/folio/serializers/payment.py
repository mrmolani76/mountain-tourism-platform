"""Payment serializers."""

from rest_framework import serializers

from modules.folio.models import Payment


class PaymentSerializer(serializers.ModelSerializer):
    class Meta:
        model = Payment
        fields = [
            'id', 'folio', 'method', 'amount', 'reference',
            'is_voided', 'voided_at', 'voided_reason', 'voided_by',
            'created_at', 'updated_at',
        ]
        read_only_fields = [
            'id', 'is_voided', 'voided_at', 'voided_reason',
            'voided_by', 'created_at', 'updated_at',
        ]


class PaymentCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Payment
        fields = ['folio', 'method', 'amount', 'reference']

    def validate_amount(self, value):
        if value <= 0:
            raise serializers.ValidationError('Amount must be positive.')
        return value


class PaymentVoidSerializer(serializers.Serializer):
    reason = serializers.CharField(max_length=200)
    voided_by = serializers.IntegerField()
