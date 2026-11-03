# Change Report: Module 1 code gaps — Owner create, AuthZ, Hotel remove, tests

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-code-gaps

## 1. Summary
Closed remaining Laravel Module 1 *code* gaps from deep-analysis DoD: Owner staff on tenant create, shared StaffAuthz + outlet-scoped outlet reads, removed Hotel stub, added unit/contract tests (not run).

## 2. Before
- `TenantService::register` ignored `owner_email` / `owner_password`
- Controllers duplicated `ensureOwner()`; no outlet-access helper
- `Modules/Hotel` stub enabled in `modules_statuses.json`
- Almost no Laravel tests for Module 1

## 3. After
- `TenantService::register` returns `{tenant, owner_created}`; `createOwnerStaff()` when tenant DB ready
- `StaffAuthz` + `Staff::hasOutletAccess`; Outlet index/show scoped; mutations Owner-only
- Modules = Saas + Core only
- Tests: `StaffRoleTest`, `TenantServiceOwnerContractTest`, `TenantIsolationContractTest` (skips unless `TENANT_ISOLATION_E2E=1`)

## 4. Reason
User asked to complete remaining Module 1 code (no setup on this machine).

## 5. Files Affected
- `Modules/Saas/App/Services/TenantService.php`
- `Modules/Saas/App/Http/Controllers/TenantController.php`
- `Modules/Core/App/Support/StaffAuthz.php`
- `Modules/Core/App/Models/Staff.php`
- `Modules/Core/App/Http/Controllers/{Outlet,Building,Staff,Season,Settings,OutletAccess,Auth}Controller.php`
- `modules_statuses.json` (Hotel removed)
- `Modules/Hotel/**` (deleted)
- `tests/Unit/StaffRoleTest.php`
- `tests/Unit/TenantServiceOwnerContractTest.php`
- `tests/Feature/TenantIsolationContractTest.php`
- `work/changes/2026-11-03--71-module1-code-gaps-owner-authz-hotel.md`

## 6. Verification / Status
- [ ] Tested / Verified? No — code-only; `php -l` on patched controllers OK
- [ ] Migrations/Scripts pending? Owner create needs tenant DB migrated first
- [ ] Notes: Item 71 Verified AC still needs setup-machine smoke + `TENANT_ISOLATION_E2E=1`
