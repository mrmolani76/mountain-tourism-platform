# Work Plan

Rows move between tables. An ID appears in **exactly one** table, matching its folder.

## Phases

See `work/phases/`. **Current focus: backend / API only** (frontend deferred).

| Phase | File | Status |
|-------|------|--------|
| 1 Foundation | `work/phases/01-foundation.md` + `Module_1_*.md` | Closeout open (kanban **29–33**); 02–10 + 28 done |
| 2 Hotel and folio | `work/phases/02-hotel-and-folio.md` + Module_2/3 | Planned depth: kanban **35–44** |
| 3 POS, rental, services | `work/phases/03-pos-rental-services.md` + Module_4/5/6 | Planned depth: kanban **45–55** |
| 4 Transport, lifts, weather | `work/phases/04-transport-lifts-weather.md` + Module_7/8 | Planned depth: kanban **56–63** |
| 5 Dashboard and handover | `work/phases/05-dashboard-and-handover.md` + Module_9 | Planned depth: kanban **64–66** |

## Todo

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| 30 | Module 1 — audit foundation (AuditEvent + service) | high | A-core | backend | M |
| 31 | Module 1 — edge authz tests (inactive tenant + SaaS 403) | high | A-core | tests | S |
| 32 | Module 1 — MVP policy defaults for open questions | medium | A-core | docs | S |
| 33 | Module 1 — exit gate and mark complete | high | A-core | docs | S |
| 34 | Fix domain ViewSets auth (hotel…reports) | high | A-core | backend | M |
| 35 | Module 2 — HutBed, season prices, Reservation APIs | high | B-hotel | backend | L |
| 36 | Module 2 — check-in opens Stay + Folio | high | B-hotel | backend | M |
| 37 | Module 2 — nightly room + board posting job | high | B-hotel | backend | L |
| 38 | Module 2 — calendar and availability APIs | medium | B-hotel | backend | M |
| 39 | Module 2 — acceptance tests and exit gate | high | B-hotel | tests | M |
| 40 | Module 3 — integer minor-unit money on Charge/Payment | high | C-folio | backend | M |
| 41 | Module 3 — manager void + audit writers | high | C-folio | backend | M |
| 42 | Module 3 — charge-to-room lookup by room/guest | high | C-folio | backend | M |
| 43 | Module 3 — hard zero-balance checkout + invoice payload | high | C-folio | backend | M |
| 44 | Module 3 — acceptance tests and exit gate | high | C-folio | tests | M |
| 45 | Module 4 — season menu visibility + order snapshots | medium | D-pos | backend | M |
| 46 | Module 4 — board check + charge-to-room / pay-now | high | D-pos | backend | L |
| 47 | Module 4 — packed lunch + cashier shift | medium | D-pos | backend | M |
| 48 | Module 4 — acceptance tests and exit gate | high | D-pos | tests | M |
| 49 | Module 5 — item types, items, season day rates | high | E-rental | backend | M |
| 50 | Module 5 — rent out, return, double-book, late day | high | E-rental | backend | L |
| 51 | Module 5 — acceptance tests and exit gate | high | E-rental | tests | M |
| 52 | Module 6 — service config + weekday/season slots | high | F-services | backend | M |
| 53 | Module 6 — booking, capacity, staff conflict, cancel void | high | F-services | backend | L |
| 54 | Module 6 — guided outing headcount close rules | high | F-services | backend | M |
| 55 | Module 6 — acceptance tests and exit gate | high | F-services | tests | M |
| 56 | Module 7 — trips, seats, driver/vehicle conflict | high | G-transport | backend | L |
| 57 | Module 7 — reservation arrival creates pickup | high | G-transport | backend | M |
| 58 | Module 7 — Lift status history + lift passes | high | G-transport | backend | L |
| 59 | Module 7 — acceptance tests and exit gate | high | G-transport | tests | M |
| 60 | Module 8 — weather points + MET hourly fetch | high | H-weather | backend | L |
| 61 | Module 8 — limits evaluate → warnings on ops | high | H-weather | backend | L |
| 62 | Module 8 — cancel for weather + avalanche + lobby API | high | H-weather | backend | L |
| 63 | Module 8 — acceptance tests and exit gate | high | H-weather | tests | M |
| 64 | Module 9 — Today dashboard aggregates API | high | I-dashboard | backend | L |
| 65 | Module 9 — date-range reports + CSV + cent match | high | I-dashboard | backend | L |
| 66 | Module 9 — exit gate and MVP modules complete | high | I-dashboard | docs | S |

## In Progress

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|

## Completed

| ID | Title | Priority | Module | Area | Effort | Completed |
|----|-------|----------|--------|------|--------|-----------|
| 29 | Module 1 — freeze tenancy decision (DB-per-tenant) | high | A-core | docs | S | 2026-11-03 |
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
