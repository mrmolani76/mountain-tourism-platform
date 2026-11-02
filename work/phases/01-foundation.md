# Phase 1 — Foundation (backend focus)

- **PDF**: Milestone 1 (§8)
- **Weeks**: 1–2
- **Payment**: 15%
- **Modules**: A (Core and super admin)
- **Depends on**: none
- **Kanban IDs**: 02–10 (seeded in `work/todo/`)
- **Focus**: backend / API / tests / VPS+CI for API only

## Goal

Stand up the **Django + DRF** skeleton: data model + architecture note, auth API, multi-tenant core (buildings, outlets, staff/roles, seasons, settings), super-admin APIs, CI/CD deploy path for the API.

## In scope — backend now

- [ ] Data model and short architecture note for client review
- [ ] `backend/` Django + DRF + PostgreSQL + `.env.example`
- [ ] Tenant isolation in one place + automated cross-tenant test
- [ ] Login API (email/password); staff password reset by owner/manager (no email)
- [ ] Tenants; every business table carries `tenant_id`
- [ ] Buildings (type, altitude, map position); outlets; seasons; settings (currency, tax, timezone, units)
- [ ] Staff with role + outlet access (API + permissions)
- [ ] Super admin APIs: create complex + owner; activate/deactivate
- [ ] Audit log foundation for charge/payment/void (models/hooks; money in Phase 2)
- [ ] OpenAPI (Swagger) for shipped endpoints
- [ ] VPS setup script/guide for API process (systemd/gunicorn + Nginx + Postgres)
- [ ] GitHub Actions: PR lint/test; main deploy + health + rollback for API

## Deferred (frontend)

- [ ] Nuxt/Vuetify app and staff/super-admin **screens** (same features via UI later)

## Out of scope this phase

- Hotel/folio/POS/rental/services/transport/lifts/weather/dashboard (Phases 2–5)
- All §9 after-MVP items

## Acceptance (done when)

Two complexes on the same server cannot see each other's data, proven by an **automated test** (PDF §5A).

Also: architecture note ready; API auth + super-admin + core resources working; deploy path documented/tested for backend.

## Hard constraints (§6 / §7)

- No Docker required for MVP VPS install
- Tenant isolation in **one place** (RLS or scoped manager)
- Permissions on the API
- Secrets only in `.env`
- Builds on GitHub Actions
- Stack now: **Django + DRF + PostgreSQL** (Nuxt later)

## Work packages (backend)

| ID | Package | Area |
|----|---------|------|
| 02 | Architecture note + core data model draft | docs |
| 03 | Django + DRF backend scaffold, Postgres, `.env.example` | backend |
| 04 | Tenant isolation layer + automated cross-tenant test | backend / tests |
| 05 | Auth API: login, roles, outlet access, staff password reset (no email) | backend |
| 06 | Buildings, outlets, seasons, tenant settings APIs | backend |
| 07 | Super admin APIs: create complex + owner, activate/deactivate | backend |
| 08 | VPS setup script/guide for API (user, DB, Nginx, systemd) | devops |
| 09 | GitHub Actions: backend lint/test; deploy + health + rollback | devops |
| 10 | Foundation seed for §2 demo buildings (partial) | backend |

## Status

- Focus: backend only
- Kanban: 02–10 in `work/todo/` until picked up
