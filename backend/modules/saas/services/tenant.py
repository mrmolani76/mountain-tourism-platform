"""SaaS business logic (thin ViewSets call here)."""

from django.contrib.auth.hashers import check_password, make_password
from rest_framework.exceptions import AuthenticationFailed, PermissionDenied

from modules.saas.models import SaasUser, Tenant
from tenancy.provision import register_tenant


def authenticate_saas_user(*, email: str, password: str) -> SaasUser:
    try:
        user = SaasUser.objects.get(email=email)
    except SaasUser.DoesNotExist:
        raise AuthenticationFailed('Invalid credentials.')
    if not check_password(password, user.password):
        raise AuthenticationFailed('Invalid credentials.')
    if not user.is_active:
        raise PermissionDenied('SaaS user inactive.')
    return user


def create_saas_user(*, email: str, password: str) -> SaasUser:
    return SaasUser.objects.create(
        email=email,
        password=make_password(password),
        is_active=True,
    )


def register_complex(*, name: str, slug: str, base_domain: str) -> Tenant:
    """Central registry only — no createdb/migrate/seed."""
    return register_tenant(name=name, slug=slug, base_domain=base_domain)


def set_tenant_active(tenant: Tenant, *, is_active: bool) -> Tenant:
    tenant.is_active = is_active
    tenant.save(update_fields=['is_active', 'updated_at'])
    return tenant
