# Master Dashboard

Refresh on every work / bug / change status update.

**Focus:** Module I (**89**) done. Pending setup: Local MySQL (**90**) on another machine. Leftovers: Hotel **72** verify, lobby TV polish, handover. PDF: `document/Mountain_MVP_Scope_v1.pdf`.

## 1. Work

### Todo
- **90** — Local MySQL switch for browser testing (high). Returned 2026-11-14; finish on another machine (EQMS hosts, DB-per-tenant).
- **72** — Module 2 — Hotel, chalets and huts (Laravel) + minimal folio contract (high). Product code ready; migrate + `php artisan test` deferred (no setup).

### In Progress
- (none)

### Completed
- **89** — Module I — Owner dashboard and reports (Laravel + Nuxt) (high, 2026-11-14): Today + range/CSV + Nuxt.
- **88** — Weather frontend — points, limits, warnings, cancel (high, 2026-11-14): Nuxt weather desk + menu.
- **83** — Module H — Weather (Laravel) (high, 2026-11-14): points/fetch/limits/warnings/cancel-for-weather voids.
- **87** — Transport frontend — trips, lifts, passenger list, passes (high, 2026-11-14): Nuxt transport desk + lifts + menu.
- **86** — Services frontend — catalog, slots, bookings, outings (high, 2026-11-14): Nuxt services desk + menu.
- **85** — Rental frontend — types, items, rent out, return (high, 2026-11-14): Nuxt rental desk + menu.
- **84** — POS frontend — catalog, shifts, orders, settle (high, 2026-11-14): Nuxt POS desk + menu.
- **82** — Folio frontend — list, detail, charge-to-room, pay, void (high, 2026-11-13): Nuxt pages + menu for Module C.
- **81** — Module G — Transport and lifts (Laravel) (high, 2026-11-13): artisan Transport, trips/lifts/pass, 10 migrations, 25 routes.
- **80** — Module F — Hotel services and activities (Laravel) (high, 2026-11-13): artisan Services, slots/bookings/outings, 9 migrations.
- **79** — Module E — Equipment rental (Laravel) (high, 2026-11-13): artisan `module:make Rental`, rent/return/late/damage, one migration per table.
- **78** — Split Pos tenant migrations — one table per file (high, 2026-11-13).
- **77** — Module D — Restaurants, huts and shop (Laravel POS) (high, 2026-11-13): artisan `module:make Pos`, catalog/shift/order/board/packed-lunch APIs.
- **76** — Module 3 — Guest folio and checkout (Laravel) full contract (high, 2026-11-13): list, charge-to-room, invoice JSON, FolioAuthz.
- **75** — Align Hotel/Core listings to EQMS DataTable pattern (high, 2026-11-06): `{resource}/list/config` + `/list`, server cells, no client listing mapper.
- **74** — EQMS menu + Core staff CRUD + Hotel DataTable listings (high, 2026-11-06): menu API, staff `/users` listing, Hotel views/listings, Spatie Data DTOs, all product lists on EQMS `DataTable`.
- **73** — Full platform DB schema design (Modules A to I) (high, 2026-11-05): `docs/DATABASE_SCHEMA.md` covers central SaaS + tenant Modules A to I, invariants, indexes, migration order, open questions.
- **71** — Laravel Module 1 — central Saas + tenant Core (Inventory pattern) (high, 2026-11-04)
- **70** — Laravel Stage C — pilot slice auth + buildings (high, 2026-11-03)
- **69** — Laravel foundation Stage B: backend-laravel/ (Laravel 13.39/PHP 8.5), pgsql 18.6 connected, envelope macros, health endpoint live, CI quality job (high, 2026-11-03)
- **68** — Laravel switch Phase 0 — Django/DRF audit: 45-endpoint API inventory, schema + auth matrix, risks; master plan saved (high, 2026-11-03)
- **67** — GitHub Actions CI/CD improvement — quality-only CI, Laravel-ready deploy guard, ruff 0 errors (high, 2026-11-03)
- **29** — Module 1 tenancy decision frozen (DB-per-tenant + Host/Domain) (high, 2026-11-03)
- **30** — Module 1 audit foundation — AuditEvent + AuditService.record(), migration `0006_add_audit_event`, `manage.py check` clean (high, 2026-11-03)
- **31** — Module 1 edge authz tests — deactivated tenant login 404 + staff→SaaS 403; full suite 40 passed (high, 2026-11-03)
- **32** — Module 1 MVP policy defaults (…19 Q3–Q18) — docs/DECISIONS.md row; Q19 resolved (high, 2026-11-03)
- **34** — Fix domain ViewSets auth (hotel…reports) — StaffSessionAuthentication on all 20 domain ViewSets; auth fixes verified; BUG-APP-005 resolved (high, 2026-11-03)
- **33** — Module 1 exit gate — §20 DoD review; backend/API MVP skeleton CLOSED (audit writers + frontend deferred) (high, 2026-11-03)
- **28** — Module 1 verification — first full test run, 38 green (high, 2026-11-03)
- **27** — Project audit complete report (post items 18–26) (high, 2026-11-03)
- **26** — Tenant dev setup — physical DB, migrate, owner seed, E2E login verify (high, 2026-11-03)
- **25** — Fix BUG-APP-001 — prod SPA blank screen (register Nuxt modules) (high, 2026-11-03)
- **24** — Docker dev setup — inventory-style dev stack (compose, docs, scripts, verify) (high, 2026-11-03)
- **23** — Frontend SPA — copy into repo and wire to Django backend (high, 2026-11-03)
- **22** — remaining modules — rental, services, transport, weather, reports DRF APIs (2026-11-02)
- **21** — pos module — Product, Order, OrderLine models + DRF APIs (2026-11-02)
- **20** — hotel module — Room, Stay, BoardPlan models + DRF APIs (2026-11-02)
- **19** — folio module — Folio, Charge, Payment models + DRF APIs (2026-11-02)
- **18** — DRF settings — pagination, filtering, JSON envelope (2026-11-02)
- **10** — Foundation seed — §2 demo buildings (partial) (2026-11-02)
- **09** — GitHub Actions — backend lint/test/deploy (2026-11-02)
- **08** — VPS setup script/guide for API (2026-11-02)
- **07** — Super admin APIs — complex + owner activate/deactivate (2026-11-02)
- **17** — Fix BUG-TENANT-001 — bind staff session to issuing tenant (2026-11-02)
- **16** — Backend structure gaps fill (pyproject, common, tests split, admin, env stub) (2026-11-02)
- **06** — Buildings, outlets, seasons, tenant settings APIs (2026-11-02)
- **15** — Backend structure gaps MD (2026-11-02)
- **14** — Audit module + business logic docs (2026-11-02)
- **13** — Project audit report (2026-11-02)
- **12** — EQMS-style modules layout (2026-11-02)
- **11** — DB-per-tenant backend layout (DRF) (2026-11-02)
- **01** — Harden work-tracker kanban workflow (2026-11-02)
- **02** — Architecture note + core data model draft (2026-11-02)
- **03** — Django + DRF backend scaffold (2026-11-02)
- **04** — Tenant isolation layer + cross-tenant test (2026-11-02)
- **05** — Auth API — login, roles, outlet access, password reset (2026-11-02)

## 2. Bugs

### Open
- **BUG-APP-006** — Dev hard refresh drops :3000 / login 502 (high, 2026-11-06)

### Verified
- **BUG-APP-004** — Staff login bad credentials 403 → 401 (low, 2026-11-03)
- **BUG-APP-003** — Season overlap same-kind only (medium, 2026-11-03)
- **BUG-APP-002** — All list endpoints 500 — pagination AttributeError (high, 2026-11-03)
- **BUG-APP-001** — Prod SPA blank screen — Nuxt modules not registered (high, 2026-11-03)
- **BUG-TENANT-001** — Cross-host session identity swap risk (medium, 2026-11-02)
- **BUG-ENV-001** — Tenant DB template clone missing OPTIONS key (high, 2026-11-02)

### Closed
- (none)

## 3. Testing
- 2026-11-02 — item 06: `manage.py check` clean; migration-model check clean; new config tests code-only (suite runs on setup machine)
- 2026-11-02 — tests rewritten for DB-per-tenant / SaaS registry (not run — code-only item 11)
- 2026-11-02 — prior shared-DB isolation/auth results obsolete after tenancy change

## 4. Changes
- 2026-11-14 — `work/changes/2026-11-14--89-laravel-reports-module-i.md` — item 89: Reports Module I
- 2026-11-14 — `work/changes/2026-11-14--88-weather-frontend-module-h.md` — item 88: Weather Nuxt UI
- 2026-11-14 — `work/changes/2026-11-14--83-laravel-weather-module-h.md` — item 83: Weather Module H APIs
- 2026-11-14 — `work/changes/2026-11-14--87-transport-frontend-module-g.md` — item 87: Transport Nuxt UI
- 2026-11-14 — `work/changes/2026-11-14--84-pos-frontend-module-d.md` — item 84: POS Nuxt UI (catalog/shifts/orders/settle)
- 2026-11-13 — `work/changes/2026-11-13--82-folio-frontend-module-c.md` — item 82: Folio Nuxt UI (list/detail/charge-to-room/pay/void)
- 2026-11-13 — `work/changes/2026-11-13--80-laravel-services-module-f.md` — item 80: artisan Services + Module F APIs
- 2026-11-13 — `work/changes/2026-11-13--79-laravel-rental-module-e.md` — item 79: artisan Rental + Module E APIs
- 2026-11-13 — `work/changes/2026-11-13--78-split-pos-migrations.md` — item 78: one Pos migration per table
- 2026-11-13 — `work/changes/2026-11-13--77-laravel-pos-module-d.md` — item 77: artisan Pos module + Module D POS APIs
- 2026-11-13 — `work/changes/2026-11-13--76-laravel-folio-module-c.md` — item 76: Folio list, charge-to-room, invoice JSON, FolioAuthz
- 2026-11-06 — `work/changes/2026-11-06--75-eqms-datatable-listing-align.md` — item 75: EQMS `list/config` + `list` for Hotel/Core; server cell rows; drop client listing-table
- 2026-11-06 — `work/changes/2026-11-06--74-eqms-menu-core-staff-hotel-datatable.md` — item 74: Core+Hotel menu, staff listing, Hotel DataTable everywhere (availability + CRUD pages)
- 2026-11-03 — `work/changes/2026-11-03--71-login-frontend-laravel-connect.md` — item 71: Nuxt login ↔ Laravel Core auth, CORS/Sanctum, nginx/compose → laravel, DevPilotSeeder roles
- 2026-11-03 — `work/changes/2026-11-03--71-eqms-complere-module-folder-structure.md` — eQMS Complere App/ module layout for Saas+Core
- 2026-11-03 — `work/changes/2026-11-03--71-module1-saas-core-inventory-align.md` — item 71: keep Saas+Core only; Inventory central/tenant routes; DRF field parity; drop B–I Modules + todos 35–66
- 2026-11-03 — `work/changes/2026-11-03--30-m1-audit-foundation.md` — item 30: AuditEvent + AuditService + migration `0006_add_audit_event`
- 2026-11-03 — `work/changes/2026-11-03--modules-b-i-kanban-todos-34-66.md` — Module B–I (+auth) todos 34–66 in `work/todo/`
- 2026-11-03 — `work/changes/2026-11-03--module1-closeout-todos-29-33.md` — Module 1 closeout todos 29–33; `phases/01-todos/` removed → `work/todo/`
- 2026-11-03 — `work/changes/2026-11-03--27-project-audit-complete-report.md` — full project audit (item 27); report at `work/audits/2026-11-03-project-audit.md`
- 2026-11-03 — `work/changes/2026-11-03--code-first-no-setup-rule.md` — standing rule: product/API code only; no Docker/VPS/CI/env setup unless user asks
- 2026-11-03 — `work/changes/2026-11-03--29-m1-tenancy-decision-freeze.md` — item 29: MVP tenancy freeze (docs-only)
- 2026-11-03 — `work/changes/2026-11-03--28-module1-test-run-fixes.md` — item 28: full test suite first run, 38 green; BUG-APP-002/003/004 fixed; isolation acceptance green
- 2026-11-03 — `work/changes/2026-11-03--27-project-audit-complete-report.md` — item 27: full project audit
- 2026-11-03 — `work/changes/2026-11-03--26-tenant-dev-setup-e2e-login.md` — tenant dev setup (item 26): lab_local DB, migrated, owner seeded, E2E staff login verified via SPA proxy
- 2026-11-03 — `work/changes/2026-11-03--25-spa-nuxt-modules-blank-screen.md` — BUG-APP-001: prod SPA blank screen fix (Nuxt modules + i18n wired); login page verified
- 2026-11-03 — `work/changes/2026-11-03--24-docker-dev-setup.md` — Docker dev setup (item 24): compose/nginx/Dockerfile fixes, docs, scripts, stack verified
- 2026-11-02 — `work/changes/2026-11-02--07-to-10-super-admin-vps-ci-seed.md` — items 07-10 (super admin, VPS, CI, seed)
- 2026-11-02 — `work/changes/2026-11-02--16-backend-structure-gaps-fill.md` — gaps fill (item 16)
- 2026-11-02 — `work/changes/2026-11-02--06-core-config-apis.md` — core config APIs (item 06) + BUG-TENANT-001
- 2026-11-02 — `work/changes/2026-11-02--15-backend-structure-gaps-md.md` — BACKEND_STRUCTURE_GAPS.md
- 2026-11-02 — `work/changes/2026-11-02--14-audit-module-business-logic-docs.md` — AUDIT_MODULE.md + services/ BL
- 2026-11-02 — `work/changes/2026-11-02--13-project-audit-report.md` — audit + DEPENDENCIES.md (no setup)
- 2026-11-02 — `work/changes/2026-11-02--12-eqms-style-modules-layout.md` — Complere-style `modules/` layout
- 2026-11-02 — `work/changes/2026-11-02--11-db-per-tenant-backend-layout.md` — DB-per-tenant + DRF layout
- 2026-11-02 — `work/changes/2026-11-02--02-m1-architecture-data-model.md` — Module A architecture note + DECISIONS rows
- 2026-11-02 — `work/changes/2026-11-02--03-m1-django-drf-scaffold.md` — backend/ Django + DRF scaffold
- 2026-11-02 — `work/changes/2026-11-02--04-m1-tenant-isolation.md` — tenant isolation layer + cross-tenant test
- 2026-11-02 — `work/changes/2026-11-02--05-m1-auth-roles-api.md` — Auth API: login, roles, outlet access, password reset
- 2026-11-02 — `work/changes/2026-11-02--05-github-actions-ci-testing.md` — CI testing workflow
- 2026-11-02 — `work/changes/2026-11-02--04-phase1-module-a-todo-list-design.md` — Module A todo list design
- 2026-11-02 — `work/changes/2026-11-02--03-phase-complete-module-descriptions.md` — phase embeds full modules A–I
- 2026-11-02 — `work/changes/2026-11-02--02-phase-work-plans-backend-focus.md` — phase plans + P1 todos (API-first)
- 2026-11-02 — `work/changes/2026-11-02--01-work-tracker-workflow.md` — work-tracker workflow
- 2026-11-02 — `work/changes/2026-11-02--ai-rules-and-skills-bootstrap.md` — AI rules/skills bootstrap
- 2026-11-02 — `work/changes/2026-11-02--22-remaining-modules-drff-apis.md` — remaining modules: rental, services, transport, weather, reports DRF APIs (item 22)
- 2026-11-02 — `work/changes/2026-11-02--21-pos-module-models-drff-apis.md` — pos module: Product, Order, OrderLine models + DRF APIs (item 21)
- 2026-11-02 — `work/changes/2026-11-02--20-hotel-module-models-drff-apis.md` — hotel module: Room, Stay, BoardPlan models + DRF APIs (item 20)
- 2026-11-02 — `work/changes/2026-11-02--19-folio-module-models-drff-apis.md` — folio module: Folio, Charge, Payment models + DRF APIs (item 19)
- 2026-11-02 — `work/changes/2026-11-02--18-drf-settings-pagination-filtering-envelope.md` — DRF settings: pagination, filtering, JSON envelope (item 18)

## 5. Deploys
- (none yet)
