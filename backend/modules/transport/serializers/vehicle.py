"""Vehicle serializers."""

from rest_framework import serializers

from modules.transport.models import Vehicle


class VehicleSerializer(serializers.ModelSerializer):
    class Meta:
        model = Vehicle
        fields = ['id', 'vehicle_type', 'license_plate', 'capacity', 'is_active',
          'created_at', 'updated_at']
        read_only_fields = ['id', 'created_at', 'updated_at']
