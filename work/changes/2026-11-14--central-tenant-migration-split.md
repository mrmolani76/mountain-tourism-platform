# Change Report: Central / tenant migration split (no module tables on central)

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: central-tenant-migration-split

## 1. Summary

Central DB (`mountain_tourism`) was running all module tenant migrations
(78 tables). Kept central = SaaS registry + framework only; module tables live
only in tenant DBs.

## 2. Before

- `config/modules.php` `'auto-discover.migrations' => true`.
- nwidart auto-registered every module's `Database/migrations/tenant/` folder.
- `php artisan migrate` on central created `core_*`, `hotel_*`, `folio_*`,
  `pos_*`, `rental_*`, `service_*`, `transport_*`, `weather_*` on central too.
- Central: 78 tables; tenant `lab_local`: 66 tables (duplicated schema).

## 3. After

- `'migrations' => false` (with comment warning; do not re-enable).
- Module providers keep the item-92 gate: `loadMigrationsFrom` only when
  top-level landlord `*_*.php` exist (currently none).
- Tenant migrations only via `tenancy.migration_parameters`
  (`tenants:provision-local`).
- Fresh central migrate: 6 migrations, 13 tables (users, cache, jobs, tokens,
  sessions + `saas_user`, `saas_tenant`, `saas_domain`).
- Tenant `lab_local`: 66 tables with fixture staff.

## 4. Reason

User: "central ka alag rakho and tenant ke liye alag rakho" (DB-per-tenant,
EQMS split).

## 5. Files Affected

- `backend-laravel/config/modules.php` (migrations auto-discover off)
- Fresh central DB + re-provisioned `lab` (runtime data, not committed)

## 6. Verification / Status

- [x] `migrate:fresh` on central: only root + Saas migrations
- [x] `tenants:provision-local lab --reset` applies all module tenant migrations
- [x] Central tables = 13 (framework + saas registry)
- [x] Tenant tables = 66 (all modules)
- [x] `/api/health/` 200
