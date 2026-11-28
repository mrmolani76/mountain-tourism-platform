# Change Report: Fix SaaS Tenant UUID Schema

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Added and applied a forward-safe central migration to restore the missing `saas_tenant.uuid` column and its constraints.

## 2. Before
- Local app environment used central PostgreSQL database `mountain_tourism`.
- `saas_tenant.uuid` did not exist, while the dashboard query selected it and returned HTTP 500.
- All prior Saas module migrations were applied; the source create-table migration already included the UUID column.

## 3. After
- Added `2026_11_28_055050_add_uuid_to_saas_tenant.php`.
- The migration explicitly targets central `pgsql`, adds a nullable UUID column if missing, backfills rows with generated UUIDs, adds a unique index if absent, and enforces `NOT NULL`.
- Rollback is intentionally blocked because tenant UUIDs become public identifiers and must not be removed.
- Applied only pending migrations from `Modules/Saas/Database/migrations` to local `mountain_tourism`. No database reset was run.
- Browser dashboard now displays 1 total tenant, 1 active, 0 inactive, and no error alert.

## 4. Reason
The active central schema had drifted from the Saas migration contract, causing the overview API to fail on its UUID selection.

## 5. Files Affected
- `backend-laravel/Modules/Saas/Database/migrations/2026_11_28_055050_add_uuid_to_saas_tenant.php`
- `work/bugs/BUG-DATA-001-saas-tenant-uuid-missing.md`
- `work/bugs/INDEX.md`
- `work/SUMMARY.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`

## 6. Verification / Status
- [x] `php artisan migrate --database=pgsql --path=Modules/Saas/Database/migrations --force` completed successfully in `mtmtp_laravel_local`.
- [x] `migrate:status` reports the new migration as `Ran`.
- [x] Schema inspection confirms `uuid` is PostgreSQL `uuid`, non-null, and uniquely indexed.
- [x] All 1 of 1 tenant rows has a UUID.
- [x] Reloaded `/saas`; dashboard rendered its counts without the previous error state.
- [ ] Automated tests were not run. Full tenant-list acceptance remains pending under item 114.
