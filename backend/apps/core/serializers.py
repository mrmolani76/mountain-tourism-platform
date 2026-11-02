"""API serializers for apps.core."""
from rest_framework import serializers

from .models import Staff, Tenant


class StaffSerializer(serializers.ModelSerializer):
    class Meta:
        model = Staff
        fields = ['id', 'email', 'tenant', 'is_active', 'created_at']
        read_only_fields = ['id', 'created_at']

    def create(self, validated_data):
        # Tenant is set by the view from request.user.tenant (never from client).
        validated_data['tenant'] = self.context['tenant']
        return super().create(validated_data)


class TenantSerializer(serializers.ModelSerializer):
    class Meta:
        model = Tenant
        fields = ['id', 'name', 'is_active', 'created_at', 'updated_at']
        read_only_fields = ['id', 'created_at', 'updated_at']
