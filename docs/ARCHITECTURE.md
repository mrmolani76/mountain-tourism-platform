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
- **Single** isolation mechanism (document choice here when implemented: RLS vs scoped manager).
- Super admin is the only cross-tenant operator.

## Money
- Integer minor units.
- Append-only Charge/Payment; voids only.
- Outlet revenue from charges; cash from payments.

## Auth
- Email/password.
- Owners/managers reset staff passwords (no email sending in MVP).
- Permissions enforced in API.

## Frontend
- Role-oriented screens; tablet-friendly POS; phone lift status; lobby TV private link.

## External free services
- MET Norway (server job).
- OSM tiles + Leaflet/MapLibre.

## Out of MVP deploy shape
- No Docker required on VPS.
- No paid APIs.
- No native apps.

## Open questions
- [ ] Exact Django project layout (`apps/` package names)
- [ ] Auth: session vs JWT for Nuxt (document when chosen)
- [ ] Tenant isolation mechanism: RLS vs manager
- [ ] Process supervisor: systemd vs PM2 for Node build artifacts only
