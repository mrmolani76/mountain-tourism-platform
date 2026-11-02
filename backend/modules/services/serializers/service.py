"""Service serializers."""

from rest_framework import serializers

from modules.services.models import Service


class ServiceSerializer(serializers.ModelSerializer):
    outlet_name = serializers.CharField(source='outlet.name', read_only=True)

    class Meta:
        model = Service
        fields = [
            'id', 'outlet', 'outlet_name', 'name', 'description',
            'duration_minutes', 'price', 'is_active',
            'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'outlet_name', 'created_at', 'updated_at']
