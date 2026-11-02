"""DRF serializers for modules.core (tenant DB)."""

from rest_framework import serializers

from modules.core.models import Staff, StaffOutletAccess


class StaffSerializer(serializers.ModelSerializer):
    class Meta:
        model = Staff
        fields = ['id', 'email', 'role', 'is_active', 'created_at']
        read_only_fields = ['id', 'created_at']


class LoginSerializer(serializers.Serializer):
    email = serializers.EmailField()
    password = serializers.CharField(write_only=True)


class MeSerializer(serializers.ModelSerializer):
    role = serializers.CharField(source='get_role_display', read_only=True)
    tenant_slug = serializers.SerializerMethodField()

    class Meta:
        model = Staff
        fields = ['id', 'email', 'role', 'is_active', 'tenant_slug']

    def get_tenant_slug(self, obj):
        tenant = self.context.get('tenant')
        return getattr(tenant, 'slug', None)


class PasswordResetSerializer(serializers.Serializer):
    staff_id = serializers.IntegerField()
    new_password = serializers.CharField(write_only=True, min_length=8)


class StaffOutletAccessSerializer(serializers.ModelSerializer):
    class Meta:
        model = StaffOutletAccess
        fields = ['id', 'staff', 'outlet', 'created_at']
        read_only_fields = ['id', 'created_at']
