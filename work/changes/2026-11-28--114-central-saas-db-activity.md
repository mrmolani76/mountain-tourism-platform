# Change Report: Central SaaS Database Activity

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Completed central DB-backed SaaS lifecycle logging for tenant creation and profile changes, while keeping tenant security events in each tenant database.

## 2. Before
- `saas_tenant_activity` stored tenant creation, Owner onboarding, and activation changes in the central database.
- Changes to a tenant's name were not recorded in the central activity history.
- The migration guide did not clearly distinguish safe audit events from generic request-payload logging.

## 3. After
- Actual tenant-name changes now create `tenant_updated` events with the changed field name only.
- API coverage checks central events for tenant creation and profile updates and verifies sensitive values are not included.
- Migration guidance documents `saas_tenant_activity` as the central SaaS audit log and `core_audit_event` as tenant-local staff security logging. Generic request-payload logging remains excluded.

## 4. Reason
The user requested Nexro's central migration/logging patterns be reviewed and the appropriate database logging added without leaking temporary credentials or crossing tenant database boundaries.

## 5. Files Affected
- `backend-laravel/Modules/Saas/App/Http/Controllers/TenantController.php`
- `backend-laravel/tests/Feature/Modules/Saas/SaasRegistryTest.php`
- `backend-laravel/database/migrations/README.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [ ] Tests run. Focused API tests are authored but not executed under the active code-only workflow.
- [ ] Browser acceptance remains pending; the user said they will test the flow.
- [x] No database reset or migration execution was performed in this change.
