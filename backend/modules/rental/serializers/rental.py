"""Rental serializers."""

from rest_framework import serializers

from modules.rental.models import Rental


class RentalSerializer(serializers.ModelSerializer):
    class Meta:
        model = Rental
        fields = [
            'id', 'outlet', 'staff', 'folio', 'item_name', 'item_sku',
            'checked_out_at', 'returned_at', 'status', 'damage_fee',
            'deposit', 'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'checked_out_at', 'created_at', 'updated_at']
