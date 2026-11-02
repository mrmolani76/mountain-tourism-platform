# Architecture note (living — Milestone 1 deliverable)

Fill this before/during Milestone 1. Keep short enough for client review.

## Goal
Pilot complex runs one full winter/summer day: pickup → check-in → rent → lifts → eat → sauna → one correct folio; staff see weather limits before guests do.

## High-level

```text
Browser (Nuxt/Vuetify staff UI) — deferred
        │ HTTPS
        ▼
Nginx → Django/DRF API
        │
        ├─ Host saas.*     → central DB (Tenant, Domain, SaasUser)
        ├─ Host {slug}.*   → tenant DB (Staff, Building, Folio, …)
        ├─ Scheduler/cron (weather fetch, nightly posting)
        └─ File/static as needed
```

## Tenancy (DB-per-tenant)
- Central DB (`default`): `modules.saas` — Tenant, Domain, SaasUser.
- Each complex: own Postgres DB named `{slug}_{env}`; no `tenant_id` on business rows.
- Resolve: `tenancy.middleware.TenantResolveMiddleware` (Host → Domain → activate alias).
- Router: `config.db_router.TenantDatabaseRouter`.
- Super admin = `SaasUser` on central host only (`/api/saas/`).
- SSO: out of scope.

## Money
- Integer minor units.
- Append-only Charge/Payment; voids only.
- Outlet revenue from charges; cash from payments.

## Auth
- Tenant host: email/password, DRF session (`StaffSessionAuthentication`).
- Central host: email/password session (`SaasSessionAuthentication`).
- Permissions on API (DRF permission classes). No SSO.

## Backend layout (`backend/`) — Complere-style modules
```text
tenancy/                 # platform (like Complere app/)
modules/                 # like Complere Modules/
  saas/                  # central registry + DRF TenantViewSet
  core/                  # Staff, outlets, auth
  hotel|folio|pos|rental|services|transport|weather|reports/
config/
tests/
```

Each module: `models/`, `services/`, `serializers/`, `views/`, `permissions/`, `urls.py`, `migrations/`, `module.json`.

- Thin DRF views/viewsets → `services/`
- Migrations append-only; saas → central; other modules → tenant DBs

## Frontend
- Deferred — backend/API only.

## External free services
- MET Norway (server job).
- OSM tiles + Leaflet/MapLibre.

## Deploy shape
- No Docker required on VPS (direct user + Postgres + Nginx + systemd).
- Process supervisor: **systemd**.
- Physical createdb/migrate/hosts = setup work (not this layout item).

## Open questions
- [ ] None remaining for Module A foundation layout
