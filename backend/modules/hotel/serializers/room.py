"""Room serializers."""

from rest_framework import serializers

from modules.hotel.models import Room


class RoomSerializer(serializers.ModelSerializer):
    building_name = serializers.CharField(source='building.name', read_only=True)
    room_type_name = serializers.CharField(source='room_type.name', read_only=True)

    class Meta:
        model = Room
        fields = [
            'id', 'building', 'building_name', 'room_type', 'room_type_name',
            'number', 'floor', 'is_active', 'is_available',
            'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'building_name', 'room_type_name', 'created_at', 'updated_at']
