---
id: BUG-DATA-001
title: Central SaaS dashboard fails when tenant UUID column is missing
status: VERIFIED
type: DATA
severity: high
created: 2026-11-28
---

# BUG-DATA-001: Central SaaS dashboard fails when tenant UUID column is missing

## 1. Summary
The SaaS dashboard API returns HTTP 500 because the active central `mountain_tourism` database has no `uuid` column on `saas_tenant`, although the application queries it.

## 2. Environment
Local development, PostgreSQL central database `mountain_tourism`, SaaS UI at `http://saas.mtmtp.local:3000`.

## 3. Steps to Reproduce
1. Sign in to the local SaaS admin.
2. Open `/saas`.
3. Load `/api/saas/dashboard`.

## 4. Expected
The dashboard returns tenant counts and recent activity with HTTP 200.

## 5. Actual
The API returns HTTP 500 with SQLSTATE `42703`: column `uuid` does not exist in `saas_tenant`. The failed query selects `id`, `uuid`, and `name`.

## 6. Evidence
- Browser request to `/api/saas/dashboard` returned HTTP 500 with the PostgreSQL error for `mountain_tourism`.
- `backend-laravel/Modules/Saas/App/Http/Controllers/TenantController.php` selects `uuid` for tenant activity details.
- `backend-laravel/Modules/Saas/Database/migrations/2026_11_03_000001_create_saas_registry_tables.php` declares a unique `uuid` column.
- The pasted response attachment confirms the failing connection and database.

## 7. Root Cause
The active central database schema is behind the application migration contract: `saas_tenant.uuid` is absent while the dashboard controller expects it.

## 8. Workaround
No workaround was needed after the forward migration restored the central schema.

## 9. Fix
Added and applied a forward-safe central migration that adds `uuid` when absent, backfills existing tenants, and enforces non-null and unique constraints. No database reset was performed.

## 10. Impact
The SaaS dashboard and tenant flows that serialize UUIDs fail in the central admin. Tenant business databases and money records are not implicated.

## 11. Related
- Work item: `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- Fix report: `work/changes/2026-11-28--114-fix-saas-tenant-uuid.md`
- Diagnosis report: `work/changes/2026-11-28--114-central-saas-uuid-schema-mismatch.md`
- Other bugs: `BUG-ENV-004`

## 12. Verification
- Migration `2026_11_28_055050_add_uuid_to_saas_tenant` ran successfully on local central database `mountain_tourism`.
- The UUID column is PostgreSQL `uuid`, non-null, and has a unique index; all 1 of 1 current tenant rows has a UUID.
- Reloaded `/saas`; dashboard rendered 1 total, 1 active, 0 inactive, with no error alert.
- Full tenant-list browser acceptance remains pending separately under item 114.
