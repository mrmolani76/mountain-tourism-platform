# Change Report: Laravel Stage C pilot slice (item 70)

- **Date**: 2026-11-03
- **Work item**: 70
- **By**: OpenCode agent

## Summary

First Stage C vertical slice ported from Django/DRF to Laravel: DB-per-tenant
middleware, staff session auth with tenant-binding guard, and the buildings
CRUD endpoint, all preserving the existing API contract (envelope, page-number
pagination, append-only 405s). Django backend untouched. Tests not run (§16).

## Before

- `backend-laravel/` booted with health + envelope macros only (item 69).
- No tenant resolution, no staff auth, no ported module.

## After

- Tenancy: `config/tenancy.php`, `TenantConnection`, `TenantResolver`, `ResolveTenant`
  + `StaffSessionAuth` middleware (exact DRF parity messages + 401/403 semantics).
- Models: `Tenant`, `Domain`, `Staff` (core_staff, tenant conn, UPDATED_AT null for
  Django parity), `Building` (core_building, ordering altitude_m nulls first + name).
- Migrations: central `saas_tenant`/`saas_domain`; tenant `core_staff`/`core_building`
  (column parity with Django).
- Dev seed: lab tenant + localhost domain + 3 staff (owner/manager/desk, password123)
  + 2 buildings in `mountain_tenant_lab`.
- `AuthController`: login (bcrypt, tenant-host required, session binding + regenerate),
  me, logout ({detail:'Logged out.'}), reset-password (owner/manager gate).
- `BuildingController`: index (paginated envelope parity), show/store/update (raw
  serializer), destroy (204), owner-gated, PUT not registered → 405.
- FormRequests: Login, Store/Update Building → 400 envelope with details map.
- `bootstrap/app.php`: StartSession prepended to api group, aliases, exception
  renders (Validation 400, NotFound 404, MethodNotAllowed 405).
- `routes/api.php`: health public, auth routes, buildings CRUD (no PUT).
- `.env`/`.env.example`: `TENANCY_CENTRAL_HOSTS=saas.localhost`.
- `config/database.php`: added `tenant` connection entry.
- Audit inventory rows #8, #10-#13, #17 → Verified (item 70).

## Verification (live, via artisan serve + curl, Host: localhost)

- GET /api/health → 200 envelope (database ok)
- GET /api/auth/me (no session) → 401 'Authentication credentials were not provided.'
- POST /api/auth/login bad creds → 401 'Invalid credentials.'
- POST login owner → 200 raw staff JSON + cookie
- GET /api/auth/me authed → 200 raw staff JSON
- GET /api/buildings → 200 paginated envelope (2 rows, exact meta keys)
- POST /api/buildings owner → 201 raw serializer
- POST /api/buildings desk → 403
- PUT /api/buildings/1 → 405 'Method "PUT" not allowed.'
- DELETE /api/buildings/{id} → 204
- POST invalid body → 400 envelope with details map
- reset-password owner→desk → 200 {detail:'Password reset.'}
- unknown host → 404 'Unknown tenant host.'
- central host (saas.localhost) me → 403
- php -l all new/modified PHP: No syntax errors.
- NO test suites executed (master plan §16).

## Open gaps flagged

- CSRF: Django enforced CSRF on authenticated unsafe methods; Laravel api group
  is CSRF-free. Frontend sends X-CSRFToken (Django format) which Laravel ignores.
  Stage D frontend rework / acceptance needed.
- Bcrypt vs Django PBKDF2 hash migration for real data (Stage D).
- Money units / /api/v1 / module gaps remain open client decisions (audit §22).
- deploy.yml still Django; rework at Stage E.
EOF
echo written