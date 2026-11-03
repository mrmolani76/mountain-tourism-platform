# Work Plan

Rows move between tables. An ID appears in **exactly one** table, matching its folder.

## Phases

See `work/phases/`. **Current focus: backend / API only** (frontend deferred).

| Phase | File | Status |
|-------|------|--------|
| 1 Foundation | `work/phases/01-foundation.md` + `01-todos/A-*.md` | Active — Module A todos 02–10 |
| 2 Hotel and folio | `work/phases/02-hotel-and-folio.md` | Planned |
| 3 POS, rental, services | `work/phases/03-pos-rental-services.md` | Planned |
| 4 Transport, lifts, weather | `work/phases/04-transport-lifts-weather.md` | Planned |
| 5 Dashboard and handover | `work/phases/05-dashboard-and-handover.md` | Planned |

## Todo

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|

## In Progress

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|

## Completed

| ID | Title | Priority | Module | Area | Effort | Completed |
|----|-------|----------|--------|------|--------|-----------|
| 25 | Fix BUG-APP-001 — prod SPA blank screen (register Nuxt modules) | high | infra | frontend | S | 2026-11-03 |
| 24 | Docker dev setup — inventory-style dev stack (compose, docs, scripts, verify) | high | infra | devops | M | 2026-11-03 |
| 23 | Frontend SPA — copy into repo and wire to Django backend | high | A-core | frontend | L | 2026-11-03 |
| 22 | remaining modules — rental, services, transport, weather, reports DRF APIs | medium | E–I | backend | L | 2026-11-02 |
| 21 | pos module — Product, Order, OrderLine models + DRF APIs | high | D-pos | backend | L | 2026-11-02 |
| 20 | hotel module — Room, Stay, BoardPlan models + DRF APIs | high | B-hotel | backend | L | 2026-11-02 |
| 19 | folio module — Folio, Charge, Payment models + DRF APIs | high | C-folio | backend | L | 2026-11-02 |
| 18 | DRF settings — pagination, filtering, JSON envelope | high | A-core | backend | M | 2026-11-02 |
| 10 | Foundation seed — §2 demo buildings (partial) | medium | A-core | backend | S | 2026-11-02 |
| 09 | GitHub Actions — backend lint/test/deploy | medium | infra | devops | L | 2026-11-02 |
| 08 | VPS setup script/guide for API | medium | infra | devops | M | 2026-11-02 |
| 07 | Super admin APIs — complex + owner activate/deactivate | high | A-core | backend | M | 2026-11-02 |
| 17 | Fix BUG-TENANT-001 — bind staff session to issuing tenant | high | A-core | backend | S | 2026-11-02 |
| 16 | Backend structure gaps fill (pyproject, common, tests split, admin, env stub) | high | infra | backend | M | 2026-11-02 |
| 15 | Backend structure gaps MD | medium | docs | docs | S | 2026-11-02 |
| 14 | Audit module + business logic docs | medium | docs | docs | S | 2026-11-02 |
| 13 | Project audit report | high | docs | docs | M | 2026-11-02 |
| 12 | EQMS-style modules layout | high | A-core | backend | M | 2026-11-02 |
| 11 | DB-per-tenant backend layout (DRF) | high | A-core | backend | L | 2026-11-02 |
| 06 | Buildings, outlets, seasons, tenant settings APIs | high | A-core | backend | M | 2026-11-02 |
| 01 | Harden work-tracker kanban workflow | high | ai-rules | docs | S | 2026-11-02 |
| 02 | Architecture note + core data model draft | high | A-core | docs | M | 2026-11-02 |
| 03 | Django + DRF backend scaffold | high | A-core | backend | M | 2026-11-02 |
| 04 | Tenant isolation layer + cross-tenant test | high | A-core | backend | M | 2026-11-02 |
| 05 | Auth API — login, roles, outlet access, password reset | high | A-core | backend | M | 2026-11-02 |
