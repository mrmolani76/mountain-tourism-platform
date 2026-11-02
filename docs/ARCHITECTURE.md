# Architecture note (living — Milestone 1 deliverable)

Fill this before/during Milestone 1. Keep short enough for client review.

## Goal
Pilot complex runs one full winter/summer day: pickup → check-in → rent → lifts → eat → sauna → one correct folio; staff see weather limits before guests do.

## High-level

```text
Browser (Nuxt/Vuetify staff UI)
        │ HTTPS
        ▼
Nginx → Django/DRF API (gunicorn/uvicorn)
        │
        ├─ PostgreSQL (shared DB, tenant-scoped access layer)
        ├─ Scheduler/cron (weather fetch, nightly posting)
        └─ File/static as needed
```

## Tenancy
- One DB, `tenant_id` on business tables.
- **Single** isolation mechanism: **scoped manager** (custom Django manager + base model), not RLS — simpler, Django-idiomatic, testable without DB setup.
- Super admin is the only cross-tenant operator.

## Money
- Integer minor units.
- Append-only Charge/Payment; voids only.
- Outlet revenue from charges; cash from payments.

## Auth
- Email/password, **session-based** (DRF `SessionAuthentication`) for MVP.
- Owners/managers reset staff passwords (no email sending in MVP).
- Permissions enforced in API.
- Token auth can be added later if Nuxt moves to a different domain.

## Backend layout (`backend/`)
- Django project `config/`
- Domain apps: `apps.core` (Tenant, Staff, Building, Outlet, Season, settings, audit stub)
- `tests/` with unit + API tests
- Migrations append-only: never edit a shipped migration

## Frontend
- Role-oriented screens; tablet-friendly POS; phone lift status; lobby TV private link.
- **Deferred** — backend/API only in Phase 1.

## External free services
- MET Norway (server job).
- OSM tiles + Leaflet/MapLibre.

## Deploy shape
- No Docker required on VPS (direct user + Postgres + Nginx + systemd).
- Process supervisor: **systemd**.
- No paid APIs. No native apps.

## Entities (Module A)
| Entity | Key fields / notes |
|--------|--------------------|
| Tenant | name, is_active; one complex |
| TenantSettings | currency, tax_rates, timezone, units |
| Staff | email, password, role, tenant_id, is_active |
| StaffOutletAccess | staff + outlet M2M-ish access |
| Building | type, name, altitude_m, map position |
| Outlet | building_id, name, type (restaurant, shop, rental, spa, sports, transport, lifts, …) |
| Season | winter/summer/closed + starts_on, ends_on |
| Audit stub | ready for charge/payment/void writers (Module C+) |

## App packages
- `apps.core` (primary; Tenant, Staff, Building, Outlet, Season, settings)
- `apps.accounts` optional split later

## Open questions
- [ ] None remaining for Module A foundation
