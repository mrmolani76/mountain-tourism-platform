# Work Plan

Rows move between tables. An ID appears in **exactly one** table, matching its folder.

## Phases

See `work/phases/`. **Current focus: backend / API only** (frontend deferred). Laravel switch: Module 1 (central Saas + tenant Core) first.

| Phase | File | Status |
|-------|------|--------|
| 1 Foundation | `work/phases/01-foundation.md` + `Module_1_*.md` | Django M1 closed (29–33); Laravel M1 = item **71** |
| 2 Hotel + folio | `work/phases/02-hotel-and-folio.md` + `Module_2_*.md` | Started: Laravel Hotel = item **72** |

## Todo

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| 72 | Module 2 — Hotel, chalets and huts (Laravel) + minimal folio contract | high | B-hotel | backend | L |

## In Progress

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| 74 | EQMS menu + Core staff CRUD wire + Hotel schema relations | high | A-core | frontend | L |

## Completed

| ID | Title | Priority | Module | Area | Effort | Completed |
|----|-------|----------|--------|------|--------|-----------|
| 73 | Full platform DB schema design (Modules A to I) | high | docs | docs | L | 2026-11-05 |
| 71 | Laravel Module 1 — central Saas + tenant Core (Inventory pattern) | high | A-core | backend | L | 2026-11-04 |
| 70 | Laravel Stage C — pilot slice (auth login/me/logout + buildings) | high | A-core | backend | L | 2026-11-03 |
| 34 | Fix domain ViewSets auth (hotel…reports) | high | A-core | backend | M | 2026-11-03 |
| 68 | Laravel switch — Phase 0 Django/DRF audit | high | A-core | docs | L | 2026-11-03 |
| 69 | Laravel foundation (Stage B) — backend-laravel | high | A-core | backend | L | 2026-11-03 |
| 67 | GitHub Actions CI/CD Improvement | high | A-core | CI/CD | L | 2026-11-03 |
| 29 | Module 1 — freeze tenancy decision (DB-per-tenant) | high | A-core | docs | S | 2026-11-03 |
| 30 | Module 1 — audit foundation (AuditEvent + service) | high | A-core | backend | M | 2026-11-03 |
| 31 | Module 1 — edge authz tests (inactive tenant + SaaS 403) | high | A-core | tests | S | 2026-11-03 |
| 32 | Module 1 — MVP policy defaults for open questions | medium | A-core | docs | S | 2026-11-03 |
| 33 | Module 1 — exit gate and mark complete | high | A-core | docs | S | 2026-11-03 |
| 28 | Module 1 verification — run DB-per-tenant suite, fix failures | high | A-core | tests | L | 2026-11-03 |
| 27 | Project audit complete report (post items 18–26) | high | docs | docs | M | 2026-11-03 |
| 26 | Tenant dev setup — physical DB, migrate, owner seed, E2E login verify | high | infra | devops | M | 2026-11-03 |
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
