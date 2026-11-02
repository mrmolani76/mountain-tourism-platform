"""Weather serializers."""

from rest_framework import serializers

from modules.weather.models import WeatherLimit, WeatherWarning


class WeatherLimitSerializer(serializers.ModelSerializer):
    class Meta:
        model = WeatherLimit
        fields = ['id', 'limit_type', 'threshold_value', 'threshold_unit', 'is_active', 'created_at', 'updated_at']
        read_only_fields = ['id', 'created_at', 'updated_at']


class WeatherWarningSerializer(serializers.ModelSerializer):
    limit_type = serializers.CharField(source='weather_limit.limit_type', read_only=True)

    class Meta:
        model = WeatherWarning
        fields = [
            'id', 'weather_limit', 'limit_type', 'affected_area', 'description',
            'current_value', 'raised_at', 'resolved_at', 'is_active',
            'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'limit_type', 'raised_at', 'created_at', 'updated_at']
