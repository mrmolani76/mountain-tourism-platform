# Work Plan

Rows move between tables. An ID appears in **exactly one** table, matching its folder.

## Phases

See `work/phases/`. **Current focus: fullstack** (Laravel API + Nuxt UI together). Module features end-to-end.

| Phase | File | Status |
|-------|------|--------|
| 1 Foundation | `work/phases/01-foundation.md` + `Module_1_*.md` | Django M1 closed (29–33); Laravel M1 = item **71** |
| 2 Hotel + folio | `work/phases/02-hotel-and-folio.md` + `Module_2_*.md` | Started: Laravel Hotel = item **72** |

## Todo

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|
| 109 | Schedule tenant-local nightly and weather jobs | high | H-weather | backend | L |
| 110 | Add minimum age to bookable services | medium | F-services | backend | M |
| 111 | Connect reservation departures to transport drop-offs | high | G-transport | backend | M |
| 112 | Implement approved OSM maps for locations and routes | high | G-transport | frontend | L |
| 113 | Build public weather lobby screen with lift status | high | H-weather | frontend | M |
| 114 | Build Nexro-style SaaS super-admin UI and tenant onboarding | high | A-core | backend/frontend | L |
| 115 | Add automated acceptance tests for Modules D through I | high | docs | tests | L |
| 116 | Document API with OpenAPI and align current project docs | high | docs | docs | L |
| 117 | Implement tenant-aware VPS deploy backup and rollback | high | infra | devops | L |
| 118 | Build complete MVP demo tenant seed | medium | A-core | backend | L |
| 119 | Complete MVP handover checklist and walkthrough | medium | docs | docs | M |
| 121 | Browser / E2E testing for frontend (Nuxt + Vuetify) | high | A-core | frontend | L |
| 103 | Manual browser acceptance checks, one module at a time | high | A-core | tests | L |
| 90 | Local MySQL switch for browser testing | high | infra | devops | S |
| 72 | Module 2 — Hotel, chalets and huts (Laravel) + minimal folio contract | high | B-hotel | backend | L |

## In Progress

| ID | Title | Priority | Module | Area | Effort |
|----|-------|----------|--------|------|--------|

## Completed

| ID | Title | Priority | Module | Area | Effort | Completed |
|----|-------|----------|--------|------|--------|-----------|
| 124 | Align Nexro agent rules and skills | high | docs | docs | L | 2026-11-27 |
| 108 | Protect folio ledger integrity and outlet attribution | high | C-folio | backend | L | 2026-11-27 |
| 120 | Fix tenant-scoped validation rule builders | high | A-core | backend | L | 2026-11-27 |
| 122 | Configure Gmail SMTP todo notifications | medium | infra | devops | S | 2026-11-27 |
| 107 | Confirm unresolved MVP contract decisions | high | docs | docs | S | 2026-11-27 |
| 106 | Fix generic folio void reason default | high | C-folio | backend | S | 2026-11-27 |
| 105 | Audit MVP scope against the complete project | high | docs | docs | L | 2026-11-26 |
| 104 | Fix missing header row in Core Staff listing | medium | A-core | backend/frontend | S | 2026-11-26 |
| 102 | Folio EQMS list/config + DataTable (Module C) | high | C-folio | backend | M | 2026-11-23 |
| 101 | All-modules EQMS listing audit + Hotel align | high | B-hotel | backend | M | 2026-11-16 |
| 100 | Align Core listings to EQMS list/config pattern | high | A-core | backend | M | 2026-11-16 |
| 99 | Complete Core Staff (list CRUD reset outlet access + listing fix) | high | A-core | backend | M | 2026-11-16 |
| 98 | Core menu UI+backend parity + allow fullstack delivery | high | A-core | backend | M | 2026-11-16 |
| 97 | Redesign staff home dashboard (today operations) | high | I-dashboard | frontend | M | 2026-11-16 |
| 96 | Core UI complete — settings, outlets, owner permissions | high | A-core | frontend | M | 2026-11-16 |
| 95 | Fix SPA API timeout — proxy target 127.0.0.1 (keep Host) | high | A-core | frontend | S | 2026-11-16 |
| 94 | Nexro-strict central DB tables (no Laravel users on landlord) | high | A-core | backend | S | 2026-11-14 |
| 93 | Nexro-style mtmp.local setup guide + hosts sync scripts | high | docs | docs | M | 2026-11-14 |
| 92 | EQMS/Nexro-style tenancy routes + middleware | high | A-core | backend | M | 2026-11-14 |
| 91 | Multi-tenant local separation doc (EQMS + Nexro) | medium | docs | docs | S | 2026-11-14 |
| 89 | Module I — Owner dashboard and reports (Laravel + Nuxt) | high | I-dashboard | backend | L | 2026-11-14 |
| 88 | Weather frontend — points, limits, warnings, cancel | high | H-weather | frontend | M | 2026-11-14 |
| 83 | Module H — Weather (Laravel) | high | H-weather | backend | L | 2026-11-14 |
| 87 | Transport frontend — trips, lifts, passenger list, passes | high | G-transport | frontend | M | 2026-11-14 |
| 86 | Services frontend — catalog, slots, bookings, outings | high | F-services | frontend | M | 2026-11-14 |
| 85 | Rental frontend — types, items, rent out, return | high | E-rental | frontend | M | 2026-11-14 |
| 84 | POS frontend — catalog, shifts, orders, settle | high | D-pos | frontend | M | 2026-11-14 |
| 82 | Folio frontend — list, detail, charge-to-room, pay, void | high | C-folio | frontend | M | 2026-11-13 |
| 81 | Module G — Transport and lifts (Laravel) | high | G-transport | backend | L | 2026-11-13 |
| 80 | Module F — Hotel services and activities (Laravel) | high | F-services | backend | L | 2026-11-13 |
| 79 | Module E — Equipment rental (Laravel) | high | E-rental | backend | L | 2026-11-13 |
| 78 | Split Pos tenant migrations — one table per file | high | D-pos | backend | S | 2026-11-13 |
| 77 | Module D — Restaurants, huts and shop (Laravel POS) | high | D-pos | backend | L | 2026-11-13 |
| 76 | Module 3 — Guest folio and checkout (Laravel) full contract | high | C-folio | backend | L | 2026-11-13 |
| 75 | Align Hotel/Core listings to EQMS DataTable pattern | high | B-hotel | frontend | M | 2026-11-06 |
| 74 | EQMS menu + Core staff CRUD wire + Hotel schema relations | high | A-core | frontend | L | 2026-11-06 |
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
