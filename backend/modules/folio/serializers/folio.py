"""Folio serializers."""

from rest_framework import serializers

from modules.folio.models import Folio


class FolioSerializer(serializers.ModelSerializer):
    class Meta:
        model = Folio
        fields = [
            'id', 'guest_name', 'stay_reference', 'status', 'currency',
            'total_amount', 'created_at', 'updated_at',
            'voided_at', 'voided_reason', 'voided_by',
        ]
        read_only_fields = ['id', 'total_amount', 'created_at', 'updated_at']


class FolioCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Folio
        fields = ['guest_name', 'stay_reference', 'currency']


class FolioCloseSerializer(serializers.Serializer):
    """Close a folio — only allowed when balance is zero."""
    confirm_zero_balance = serializers.BooleanField(
        default=False,
        help_text='Must be true to confirm folio balance is zero.',
    )
