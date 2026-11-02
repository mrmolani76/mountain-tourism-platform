"""SaaS business logic (thin ViewSets call here)."""

from django.contrib.auth.hashers import check_password, make_password
from django.db import transaction
from rest_framework.exceptions import AuthenticationFailed, PermissionDenied

from modules.core.models import Staff, StaffRole, TenantSettings
from modules.saas.models import SaasUser, Tenant
from tenancy import connections, context
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


def register_complex(
    *, name: str, slug: str, base_domain: str,
    owner_email: str = None, owner_password: str = None,
) -> Tenant:
    """Central registry + tenant DB owner + default settings (atomic).

    Creates Tenant + Domain on central DB, then registers the tenant DB alias
    and creates an owner Staff account + default TenantSettings on the tenant DB.
    Physical DB create/migrate is an ops step; this assumes the DB exists.
    """
    tenant = register_tenant(name=name, slug=slug, base_domain=base_domain)

    # Create owner + default settings on the tenant DB if credentials provided.
    if owner_email and owner_password:
        alias = connections.ensure_tenant_connection(tenant)
        context.set_current_tenant(tenant, alias=alias)
        try:
            with transaction.atomic(using=alias):
                Staff.objects.create(
                    email=owner_email,
                    password=make_password(owner_password),
                    role=StaffRole.OWNER,
                    is_active=True,
                )
                # Default settings (singleton, created lazily if missing).
                if not TenantSettings.objects.exists():
                    TenantSettings.objects.create()
        finally:
            context.clear_current_tenant()

    return tenant


def set_tenant_active(tenant: Tenant, *, is_active: bool) -> Tenant:
    tenant.is_active = is_active
    tenant.save(update_fields=['is_active', 'updated_at'])
    return tenant
