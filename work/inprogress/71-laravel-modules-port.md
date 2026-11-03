---
title: Laravel Module 1 — central Saas + tenant Core (Inventory pattern)
id: 71
priority: high
status: inprogress
milestone: 1
module: A-core
area: backend
effort: L
dependencies: 70
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Laravel Module 1 — central Saas + tenant Core

## What to do (What)
Port PDF Module A only into `backend-laravel/` using InventorySystem-docker tenancy split:

- **Central = Saas** (`Modules/Saas`, `routes/api.php`) — super admin, tenants, domains, activate/deactivate
- **Tenant modules start with Core** (`Modules/Core`, `routes/tenant.php`) — staff auth, buildings, outlets, seasons, staff, outlet-access, settings, audit

Code only on this machine (no composer / docker / migrate). B–I modules deferred until Module 1 Verified.

## Current / Before
- Item 70 pilot under `App\Http\Controllers\Api`
- Extra Modules/{Hotel…Reports} stubs existed then removed
- Kanban 35–66 (B–I depth) removed from todo

## Expected / After
- Only `Modules/{Saas,Core}` on disk
- Inventory-shaped route split: central `api.php` + tenant `tenant.php`
- DRF field parity for Module 1 models (hostname, latitude/longitude, Season without name, AuditEvent action/entity_*)
- Providers registered; `saas.auth` alias wired
- Django `backend/` kept until cutover
- SPA HTTP/auth matches eQMS qualitycenter-spa (`API` + `useAuthService` + host helpers + docs)

## Why
Client Laravel switch. Inventory = one central SaaS DB + per-tenant DBs. PDF Module A = Core + super admin. One central SaaS module; remaining PDF areas are later tenant modules.

## Acceptance Criteria (complete only when all ticked)
- [x] Non–Module-1 Laravel Modules removed
- [x] Central Saas + tenant Core layout wired (Inventory pattern)
- [x] Owner-on-create + StaffAuthz + Hotel stub removed (code gaps)
- [x] Core Form Requests + Outlet TYPES (16) + Audit write wiring + foundation seeder command
- [x] Module 1 APIs Verified on setup machine (composer + migrate + smoke + TENANT_ISOLATION_E2E)
- [x] Change report + PLAN/SUMMARY (partial reports exist; final closeout after verify)
- [x] No machine setup runs on this agent host

## Notes / Follow-ups
- 2026-11-03 Core parity pass: Form Requests, CoreAudit on Core mutations + login, `auth/me` outlet_ids, `core:seed-foundation`, Outlet::TYPES from Django.

## Files Likely Affected
- `backend-laravel/Modules/{Saas,Core}/**`
- `backend-laravel/routes/{api,tenant}.php`
- `backend-laravel/bootstrap/{app,providers}.php`
- `backend-laravel/database/migrations/**`

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
