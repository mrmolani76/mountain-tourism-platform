"""API serializers for apps.core."""
from django.contrib.auth import authenticate
from rest_framework import serializers

from .models import Staff, StaffOutletAccess, Tenant


class StaffSerializer(serializers.ModelSerializer):
    class Meta:
        model = Staff
        fields = ['id', 'email', 'role', 'tenant', 'is_active', 'created_at']
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


class LoginSerializer(serializers.Serializer):
    email = serializers.EmailField()
    password = serializers.CharField(write_only=True)


class MeSerializer(serializers.ModelSerializer):
    role = serializers.CharField(source='get_role_display', read_only=True)

    class Meta:
        model = Staff
        fields = ['id', 'email', 'role', 'tenant', 'is_active']


class PasswordResetSerializer(serializers.Serializer):
    staff_id = serializers.IntegerField()
    new_password = serializers.CharField(write_only=True, min_length=8)


class StaffOutletAccessSerializer(serializers.ModelSerializer):
    class Meta:
        model = StaffOutletAccess
        fields = ['id', 'staff', 'outlet', 'created_at']
        read_only_fields = ['id', 'created_at']
