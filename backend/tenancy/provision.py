"""
Tenant provision helpers (stubs).

Creates registry rows in code paths; physical DB create + migrate are NOT run here
(user: code only, no setup). Callers can wire ops later.
"""

from django.db import transaction

from modules.saas.models import Domain, Tenant
from tenancy.connections import ensure_tenant_connection, tenant_alias


def derive_db_name(slug: str, env: str = 'local') -> str:
    """EQMS-style: {slug}_{env}."""
    safe = slug.strip().lower().replace('-', '_')
    return f'{safe}_{env}'


def derive_hostname(slug: str, base_domain: str = 'local.mountain.local') -> str:
    return f'{slug.strip().lower()}.{base_domain}'


@transaction.atomic(using='default')
def register_tenant(*, name: str, slug: str, env: str = 'local', base_domain: str = 'local.mountain.local') -> Tenant:
    """
    Insert Tenant + primary Domain on the central DB and register DATABASES alias.

    Does not createdb / migrate / seed owner.
    """
    db_name = derive_db_name(slug, env=env)
    tenant = Tenant.objects.create(
        name=name,
        slug=slug.strip().lower(),
        db_name=db_name,
        is_active=True,
    )
    Domain.objects.create(
        tenant=tenant,
        hostname=derive_hostname(slug, base_domain=base_domain),
        is_primary=True,
    )
    ensure_tenant_connection(tenant)
    return tenant


def provision_tenant_database(tenant: Tenant) -> str:
    """
    Stub: would create the physical Postgres database named tenant.db_name.

    Raises NotImplementedError so setup is never accidental from app code.
    """
    raise NotImplementedError(
        f'Physical DB create for {tenant.db_name!r} is ops/setup — not run from app code. '
        f'Alias would be {tenant_alias(tenant.pk)!r}.'
    )
