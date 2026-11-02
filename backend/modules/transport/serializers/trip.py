"""Trip serializers."""

from rest_framework import serializers

from modules.transport.models import Trip


class TripSerializer(serializers.ModelSerializer):
    from_building_name = serializers.CharField(source='from_building.name', read_only=True)
    to_building_name = serializers.CharField(source='to_building.name', read_only=True)
    vehicle_plate = serializers.CharField(source='vehicle.license_plate', read_only=True)
    driver_name = serializers.CharField(source='driver.name', read_only=True)

    class Meta:
        model = Trip
        fields = [
            'id', 'vehicle', 'vehicle_plate', 'driver', 'driver_name',
            'from_building', 'from_building_name', 'to_building', 'to_building_name',
            'starts_at', 'ends_at', 'status', 'passenger_count', 'notes',
            'created_at', 'updated_at',
        ]
        read_only_fields = [
            'id', 'vehicle_plate', 'driver_name', 'from_building_name',
            'to_building_name', 'created_at', 'updated_at',
        ]
