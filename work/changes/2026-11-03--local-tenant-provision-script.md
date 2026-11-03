# Change Report: Local tenant provision script (eQMS-style)

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-local-tenant-script

## 1. Summary
Added eQMS-`resetLocalDatabase.sh`-style local tenant provision: artisan `tenants:provision-local` + `scripts/reset-local-tenant.sh`, plus login test checklist MD.

## 2. Before
- Only `DevPilotSeeder` (registry + seed); no create-DB / migrate-tenant ops script.
- `TenantConnection::useRegistryRow` missing (seeder called it).
- DevPilot `db_name` was `mountain_tenant_lab` (not eQMS `{slug}_local`).

## 3. After
- Command creates `{slug}_local` Postgres DB, central tenant+domain, tenant migrations, fixture staff.
- Shell wrapper detects `mtmtp_laravel` Docker like eQMS detects `complere_app`.
- `useRegistryRow` implemented; DevPilot uses `lab_local`.
- `frontend/docs/LOGIN_TEST_CHECKLIST.md` + LOCAL_SETUP link.

## 4. Reason
User asked to check eQMS local tenant flow and write a Mountain script for local tenant + login testing.

## 5. Files Affected
- `backend-laravel/app/Console/Commands/ProvisionLocalTenantCommand.php`
- `backend-laravel/app/Tenancy/TenantConnection.php`
- `backend-laravel/database/seeders/DevPilotSeeder.php`
- `scripts/reset-local-tenant.sh`
- `frontend/docs/LOGIN_TEST_CHECKLIST.md`
- `frontend/docs/LOCAL_SETUP.md`
- `work/changes/2026-11-03--local-tenant-provision-script.md`

## 6. Verification / Status
- [ ] Tested / Verified? No — code-only; run on setup machine.
- [ ] Migrations/Scripts pending? Run `./scripts/reset-local-tenant.sh lab --reset` there.
- [ ] Notes: APP_ENV=local guard; no AWS; physical createdb is intentional ops step.
