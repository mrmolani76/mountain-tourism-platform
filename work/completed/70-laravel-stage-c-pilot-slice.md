---
title: Laravel Stage C — pilot vertical slice (auth + envelope + 1 module list)
id: 70
priority: high
status: completed
milestone: none
module: A-core
area: backend
effort: L
dependencies: 69
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Laravel Stage C — pilot vertical slice

## What to do (What)
First Stage C slice per master plan §11 + audit inventory (docs/LARAVEL_SWITCH_AUDIT.md):
- Tenant-staff auth port: POST login / POST logout / GET me — session + tenant binding guard
  parity with StaffSessionAuthentication (audit §4), staff table/model per audit §3.
- DB-per-tenant middleware: host → central Domain lookup → active connection override.
- Envelope + page-number pagination parity (meta keys per audit §2).
- ONE representative list endpoint (e.g. api/buildings owner-protected) ported end-to-end.
- Manual verification checklist per contract (frontend call shapes).

## Current / After
Before: backend-laravel/ boots, health + envelope macros exist (item 69).
After: auth + tenant middleware + pagination parity + buildings slice; audit rows #10–#13, #17
moved to In progress/Verified as evidence.

## Why
Master plan Stage C; one slice at a time (principle 6).

## Acceptance Criteria
- [ ] Middleware resolves host→tenant DB (404 on unknown/inactive, mirrors current behaviour)
- [ ] Login/me/logout contract identical (401 vs 403 semantics per audit)
- [ ] Pagination meta parity
- [ ] Buildings CRUD owner-gated ported
- [ ] Audit inventory updated row-by-row
- [ ] NO changes to Django backend; NO tests in CI

## Completion Log

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Stage C pilot slice (item 70) — tenant middleware + staff auth + buildings endpoint, all on Laravel, Django untouched:
  1. Tenancy layer: `config/tenancy.php`, `TenantConnection` (runtime DB-per-tenant override, fixed central-connection resolution), `TenantResolver` (host→Domain, 404 unknown/inactive, central pass-through), `ResolveTenant` + `StaffSessionAuth` middleware (session keys staff_id/staff_email/staff_tenant_id/staff_tenant_slug; binding-mismatch→401 "Session tenant mismatch."; central→403; exact DRF parity messages).
  2. Models: `Tenant` (saas_tenant), `Domain` (saas_domain), `Staff` (core_staff, tenant conn, role labels, UPDATED_AT null for Django parity), `Building` (core_building, ordering altitude_m nulls first + name, serializeDate ISO8601 μs Z).
  3. Migrations: central `2026_11_03_000001_create_saas_registry_tables.php`; tenant `database/migrations/tenant/...` (column parity with Django core_staff/core_building).
  4. `DevPilotSeeder` (lab tenant + localhost domain + 3 staff owner/manager/desk password `password123` + 2 buildings); `DatabaseSeeder` calls it.
  5. `AuthController`: login (bcrypt Hash::check, tenant-host required, session binding + regenerate; last_login never updated), me (raw staff JSON), logout (session flush → {detail:'Logged out.'}), reset-password (owner/manager gate, 404 'Staff not found.', 400 validation).
  6. `BuildingController`: index (page-number pagination envelope parity: page,page_size,total_pages,total_count,has_next,has_previous), show/store/update (raw serializer), destroy (204 no content), owner-gated (403 'You do not have permission to perform this action.'), PUT not registered → 405 parity.
  7. FormRequests: `LoginRequest`, `StoreBuildingRequest`, `UpdateBuildingRequest` (validation → 400 envelope with details map).
  8. `bootstrap/app.php`: StartSession prepended to api group, aliases resolve.tenant/staff.auth, exception renders ValidationException→400, NotFound→404, MethodNotAllowed→405 envelope.
  9. `routes/api.php`: health public, auth login/logout/me/reset-password, buildings CRUD (no PUT).
  10. `.env`/`.env.example`: `TENANCY_CENTRAL_HOSTS=saas.localhost`. `config/database.php`: added `tenant` connection entry.
  11. `app/Support/Http/Envelope.php` shared error-envelope helper.
- **Files changed**: backend-laravel/app/**, backend-laravel/config/**, backend-laravel/database/**, backend-laravel/routes/api.php, backend-laravel/bootstrap/app.php, docs/LARAVEL_SWITCH_AUDIT.md, work trackers.
- **Verification**: live-tested via artisan serve + curl (Host: localhost):
  - GET /api/health → 200 envelope (database ok)
  - GET /api/auth/me (no session) → 401 'Authentication credentials were not provided.'
  - POST /api/auth/login bad creds → 401 'Invalid credentials.'
  - POST login owner → 200 raw staff JSON + cookie
  - GET /api/auth/me authed → 200 raw staff JSON
  - GET /api/buildings → 200 paginated envelope (2 rows, meta keys exact)
  - POST /api/buildings owner → 201 raw serializer
  - POST /api/buildings desk → 403
  - PUT /api/buildings/1 → 405 'Method "PUT" not allowed.'
  - DELETE /api/buildings/{id} → 204
  - POST invalid body → 400 envelope with details map
  - reset-password owner→desk → 200 {detail:'Password reset.'}
  - unknown host → 404 'Unknown tenant host.'
  - central host (saas.localhost) me → 403
  - php -l all new/modified PHP: No syntax errors.
  - NO test suites executed (master plan §16); tests remain in repo.
- **Change report**: work/changes/2026-11-03--70-laravel-stage-c-pilot-slice.md
- **PLAN / SUMMARY / MEMORY**: updated
