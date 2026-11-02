# Work Plan

Rows move between tables. An ID appears in **exactly one** table, matching its folder.

## Phases

See `work/phases/`. **Current focus: backend / API only** (frontend deferred).

| Phase | File | Status |
|-------|------|--------|
| 1 Foundation | `work/phases/01-foundation.md` | Active — todos 02–10 |
| 2 Hotel and folio | `work/phases/02-hotel-and-folio.md` | Planned |
| 3 POS, rental, services | `work/phases/03-pos-rental-services.md` | Planned |
| 4 Transport, lifts, weather | `work/phases/04-transport-lifts-weather.md` | Planned |
| 5 Dashboard and handover | `work/phases/05-dashboard-and-handover.md` | Planned |

## Todo

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| 02 | Architecture note + core data model draft | high | A-core | docs | M |
| 03 | Django + DRF backend scaffold | high | A-core | backend | M |
| 04 | Tenant isolation layer + cross-tenant test | high | A-core | backend | M |
| 05 | Auth API — login, roles, outlet access, password reset | high | A-core | backend | M |
| 06 | Buildings, outlets, seasons, tenant settings APIs | high | A-core | backend | M |
| 07 | Super admin APIs — complex + owner activate/deactivate | high | A-core | backend | M |
| 08 | VPS setup script/guide for API | medium | infra | devops | M |
| 09 | GitHub Actions — backend lint/test/deploy | medium | infra | devops | L |
| 10 | Foundation seed — §2 demo buildings (partial) | medium | A-core | backend | S |

## In Progress

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| | | | | | |

## Completed

| ID | Title | Priority | Module | Area | Effort | Completed |
|----|-------|----------|--------|------|--------|-----------|
| 01 | Harden work-tracker kanban workflow | high | ai-rules | docs | S | 2026-11-02 |
