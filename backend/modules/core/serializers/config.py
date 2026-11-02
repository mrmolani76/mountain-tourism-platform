"""DRF serializers for tenant configuration (settings, buildings, outlets, seasons)."""

from rest_framework import serializers

from modules.core.models import Building, Outlet, Season, TenantSettings
from modules.core.services import validate_season_overlap


class TenantSettingsSerializer(serializers.ModelSerializer):
    class Meta:
        model = TenantSettings
        fields = ['currency', 'tax_rates', 'timezone', 'units']
        read_only_fields = []


class BuildingSerializer(serializers.ModelSerializer):
    class Meta:
        model = Building
        fields = [
            'id',
            'name',
            'building_type',
            'altitude_m',
            'latitude',
            'longitude',
            'is_active',
            'created_at',
            'updated_at',
        ]
        read_only_fields = ['id', 'created_at', 'updated_at']


class OutletSerializer(serializers.ModelSerializer):
    building_name = serializers.CharField(source='building.name', read_only=True)

    class Meta:
        model = Outlet
        fields = [
            'id',
            'building',
            'building_name',
            'name',
            'outlet_type',
            'is_active',
            'created_at',
            'updated_at',
        ]
        read_only_fields = ['id', 'building_name', 'created_at', 'updated_at']


class SeasonSerializer(serializers.ModelSerializer):
    class Meta:
        model = Season
        fields = [
            'id',
            'kind',
            'starts_on',
            'ends_on',
            'is_active',
            'created_at',
            'updated_at',
        ]
        read_only_fields = ['id', 'created_at', 'updated_at']

    def validate(self, attrs):
        season = getattr(self, 'instance', None)
        kind = attrs.get('kind', getattr(season, 'kind', None))
        starts_on = attrs.get('starts_on', getattr(season, 'starts_on', None))
        ends_on = attrs.get('ends_on', getattr(season, 'ends_on', None))
        if kind is not None and starts_on is not None and ends_on is not None:
            validate_season_overlap(
                kind=kind,
                starts_on=starts_on,
                ends_on=ends_on,
                exclude_id=getattr(season, 'id', None),
            )
        return attrs
