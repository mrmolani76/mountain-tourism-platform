# Change Report: Central SaaS UUID Schema Mismatch

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Traced the SaaS dashboard HTTP 500 to a missing UUID column in the active central database and recorded the issue.

## 2. Before
- The SaaS dashboard selected `id`, `uuid`, and `name` from `saas_tenant`.
- The source central migration declares `saas_tenant.uuid` as unique.
- The active `mountain_tourism` database returned PostgreSQL `42703` because `uuid` was absent.

## 3. After
- Added `BUG-DATA-001` and updated the bug index, project summary, and active work item.
- No application code, migration, or database was changed. A forward-safe migration or explicitly approved local reset is still needed to reconcile schema.

## 4. Reason
The user asked why the SaaS platform overview would not load. The browser request showed the central database schema mismatch as the direct cause.

## 5. Files Affected
- `work/bugs/BUG-DATA-001-saas-tenant-uuid-missing.md`
- `work/bugs/INDEX.md`
- `work/SUMMARY.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`

## 6. Verification / Status
- [x] Browser-origin request to `/api/saas/dashboard` reproduced HTTP 500.
- [x] Response identified PostgreSQL SQLSTATE `42703` and missing `saas_tenant.uuid` on `mountain_tourism`.
- [x] Source migration declares the UUID column.
- [ ] Migration/reset and browser retest pending. No schema-changing operation was performed.
