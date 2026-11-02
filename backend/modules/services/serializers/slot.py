"""Slot serializers."""

from rest_framework import serializers

from modules.services.models import Slot


class SlotSerializer(serializers.ModelSerializer):
    service_name = serializers.CharField(source='service.name', read_only=True)
    staff_email = serializers.CharField(source='staff.email', read_only=True)
    available_capacity = serializers.IntegerField(read_only=True)
    is_full = serializers.BooleanField(read_only=True)

    class Meta:
        model = Slot
        fields = [
            'id', 'service', 'service_name', 'starts_at', 'ends_at',
            'capacity', 'booked_count', 'staff', 'staff_email',
            'is_active', 'available_capacity', 'is_full',
            'created_at', 'updated_at',
        ]
        read_only_fields = [
            'id', 'service_name', 'staff_email', 'available_capacity',
            'is_full', 'created_at', 'updated_at',
        ]
