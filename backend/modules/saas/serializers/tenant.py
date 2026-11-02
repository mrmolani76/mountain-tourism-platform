"""DRF serializers for central SaaS registry."""

from rest_framework import serializers

from modules.saas.models import Domain, SaasUser, Tenant


class DomainSerializer(serializers.ModelSerializer):
    class Meta:
        model = Domain
        fields = ['id', 'hostname', 'is_primary', 'created_at']
        read_only_fields = ['id', 'created_at']


class TenantSerializer(serializers.ModelSerializer):
    domains = DomainSerializer(many=True, read_only=True)

    class Meta:
        model = Tenant
        fields = [
            'id', 'name', 'slug', 'db_name', 'is_active',
            'domains', 'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'db_name', 'created_at', 'updated_at']


class TenantCreateSerializer(serializers.Serializer):
    """Register tenant + domain + optional owner (no physical DB create)."""

    name = serializers.CharField(max_length=200)
    slug = serializers.SlugField(max_length=64)
    base_domain = serializers.CharField(
        max_length=255, required=False, default='local.mountain.local'
    )
    owner_email = serializers.EmailField(required=False)
    owner_password = serializers.CharField(
        write_only=True, required=False, min_length=8
    )


class SaasLoginSerializer(serializers.Serializer):
    email = serializers.EmailField()
    password = serializers.CharField(write_only=True)


class SaasUserSerializer(serializers.ModelSerializer):
    class Meta:
        model = SaasUser
        fields = ['id', 'email', 'is_active', 'created_at']
        read_only_fields = fields
