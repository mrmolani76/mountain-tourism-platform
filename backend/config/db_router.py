"""
Route landlord (saas) models to central `default` DB.
Route tenant app models to the active tenant alias from tenancy.context.
"""

from tenancy.context import get_current_tenant_alias

LANDLORD_APP_LABELS = frozenset({'saas'})
# Platform / Django tables stay on central unless a tenant connection is active
# for tenant-owned apps only.
TENANT_APP_LABELS = frozenset({
    'core',
    'hotel',
    'folio',
    'pos',
    'rental',
    'services',
    'transport',
    'weather',
    'reports',
})


class TenantDatabaseRouter:
    """
    saas → always `default` (central).
    tenant apps → current tenant alias when set; else `default` (migrations / central tools).
    """

    def db_for_read(self, model, **hints):
        return self._db_for_model(model)

    def db_for_write(self, model, **hints):
        return self._db_for_model(model)

    def allow_relation(self, obj1, obj2, **hints):
        # Same DB only; landlord and tenant DBs do not FK across.
        db1 = self._db_for_model(obj1.__class__)
        db2 = self._db_for_model(obj2.__class__)
        return db1 == db2

    def allow_migrate(self, db, app_label, model_name=None, **hints):
        if app_label in LANDLORD_APP_LABELS:
            return db == 'default'
        if app_label in TENANT_APP_LABELS:
            # Tenant migrations run against tenant aliases (and optionally default
            # when used as a template DB). Never mix saas onto tenant DBs.
            return db != 'default' or hints.get('tenant_template', False)
        # Django contrib / sessions / etc. → central only for MVP.
        return db == 'default'

    def _db_for_model(self, model):
        label = model._meta.app_label
        if label in LANDLORD_APP_LABELS:
            return 'default'
        if label in TENANT_APP_LABELS:
            alias = get_current_tenant_alias()
            return alias or 'default'
        return 'default'
