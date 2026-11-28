# Master Dashboard

Refresh on every work / bug / change status update.

**Focus:** Item **114** is active for Nexro-style SaaS onboarding. SaaS admin login and local tenant database/Core/Owner provisioning are browser-verified; Owner email and tenant-domain login remain blocked by missing local mail transport and hostname resolution.

## 1. Work

### Todo
- **109** — Schedule tenant-local nightly and weather jobs (high). Use tenant timezone for nightly posting, hourly forecasts, and reporting dates.
- **110** — Add minimum age to bookable services (medium). Persist and validate participant age against each service.
- **111** — Connect reservation departures to transport drop-offs (high). Complete departure details and linked drop-off planning.
- **112** — Implement approved OSM maps for locations and routes (high). Wait for provider decision in 107 before replacing map workflows.
- **113** — Build public weather lobby screen with lift status (high). Show lift closures within one minute alongside warnings and avalanche level.
- **126** — Show Building names in Core Outlets listing (medium). Code is committed; focused test and authenticated browser verification remain pending.
- **115** — Add automated acceptance tests for Modules D through I (high). Cover module acceptance and cross-module folio/report outcomes.
- **116** — Document API with OpenAPI and align current project docs (high). Reconcile Laravel/Nuxt docs and resolve stale/missing handover references.
- **117** — Implement tenant-aware VPS deploy backup and rollback (high). Deliver tenant-aware deployment, seven-day backups, tested restore, and five-release rollback.
- **118** — Build complete MVP demo tenant seed (medium). Seed linked data across Modules A-H idempotently.
- **119** — Complete MVP handover checklist and walkthrough (medium). Finalize evidence and record the client walkthrough after acceptance.
- **121** — Browser / E2E testing for frontend (Nuxt + Vuetify) (high). Add Playwright coverage for auth, dashboard, availability, and checkout.
- **103** — Manual browser acceptance checks, one module at a time (high). Returned to Todo while item 123 is prioritized. Local preflight and Staff UUID listing/detail/edit browser checks pass; remaining auth and module acceptance checks are pending.
- **90** — Local MySQL switch for browser testing (high). Returned 2026-11-14; finish on another machine (EQMS hosts, DB-per-tenant).
- **72** — Module 2 — Hotel, chalets and huts (Laravel) + minimal folio contract (high). Product code ready; migrate + `php artisan test` deferred (no setup).

### In Progress
- **114** — Build Nexro-style SaaS super-admin UI and tenant onboarding (high). UUID tenant routes, temporary Owner password and forced first-login change, and DB-backed lifecycle events are in place; user browser acceptance remains pending.

### Completed
- **125** — Run platform locally with Docker Compose (medium, 2026-11-27). Nuxt dev server runs without starting the migration/reset command; live HMR verified at `http://lab.local.mtmtp.local:3000/`.
- **124** — Align Nexro agent rules and skills (high, 2026-11-27). Adapted Nexro workflows and synchronized 23 skills across Cursor, Claude, and OpenCode.
- **123** — Fix owner dashboard today-summary API and UI (medium, 2026-11-27). Fixed the invalid Eloquent Builder call, added API regression coverage, and browser-verified the dashboard summary and empty-room state.
- **108** — Protect folio ledger integrity and outlet attribution (high, 2026-11-27). Active Outlet checks, nightly mappings, retry handling, restrictive ledger FKs, and focused HTTP charge-to-room/weather-cancellation tests are complete.
- **120** — Fix tenant-scoped validation rule builders (high, 2026-11-27). Replaced unsupported connection methods; active-tenant scope, unique-price validation, weather cancellation, and charge-to-room flows passed focused HTTP tests.
- **122** — Configure Gmail SMTP todo notifications (medium, 2026-11-27). SMTP auth and delivery verified; Windows LF handling fixed.
- **107** — Confirm unresolved MVP contract decisions (high, 2026-11-27). Recorded DB-per-tenant, OSM, and MET Norway-only choices; formal tenancy clause amendment remains required before Module A acceptance.
- **106** — Fix generic folio void reason default (high, 2026-11-27). Generic voids use `other`; explicit weather remains. Focused JUnit report: 2 tests, 8 assertions, 0 failures/errors. Full Hotel test file has 8 unrelated BUG-API-002 validation failures.
- **105** — Audit MVP scope against the complete project (high, 2026-11-26). Full Modules A-I scope and relationship audit; report at `docs/MVP_SCOPE_AUDIT.md`.
- **104** — Fix missing header row in Core Staff listing (medium, 2026-11-26). Nexro `default_order` config; Vuetify headers render correctly.
- **102** — Folio EQMS list/config + DataTable (Module C) (high, 2026-11-23).
- **101** — All-modules EQMS listing audit + Hotel align (high, 2026-11-16).
- **100** — Align Core listings to EQMS list/config pattern (high, 2026-11-16).
- **99** — Complete Core Staff (CRUD, reset, outlet access + listing fix) (high, 2026-11-16).
- **98** — Core menu UI+backend parity + fullstack rule (high, 2026-11-16).
- **97** — Redesign staff home dashboard (today operations) (high, 2026-11-16): alpine today desk + reports/today.
- **96** — Core UI complete — settings, outlets, owner permissions (high, 2026-11-16).
- **95** — Fix SPA API timeout — proxy target 127.0.0.1 (high, 2026-11-16): Nuxt proxy TCP to localhost; keep browser Host for tenant.
- **94** — Nexro-strict central DB tables (high, 2026-11-14): drop landlord `users` / `password_reset_tokens`; keep `saas_*` + sessions/cache/jobs/tokens.
- **93** — Nexro-style mtmp.local setup guide + hosts sync scripts (high, 2026-11-14): `LOCAL_MTMP_SETUP.md`, `hosts:sync`, provision/env/`tenantHost` → `{slug}.mtmp.local`.
- **92** — EQMS/Nexro-style tenancy routes + middleware (high, 2026-11-14): central domain-bound SaaS; tenant prevent_central + resolve; migration gate.
- **91** — Multi-tenant local separation doc (EQMS + Nexro) (medium, 2026-11-14): `docs/MULTI_TENANT_LOCAL.md` only; no setup.
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
- **BUG-TENANT-002** — SaaS onboarding does not provision tenant database or email Owner credentials (high, RETESTING, 2026-11-28)
- **BUG-AUTH-001** — Owner provisioning can overwrite an existing staff role (high, IN_PROGRESS, 2026-11-27)
- **BUG-APP-011** — Static Core menu fallback omits Outlets (medium, BUG_CREATED, 2026-11-27)
- **BUG-APP-010** — Outlets listing shows building ID instead of name (medium, RETESTING, 2026-11-27)
- **BUG-APP-006** — Dev hard refresh drops :3000 / login 502 (high, 2026-11-06)

### Verified
- **BUG-AUTH-002** — SaaS login exceeded the old 30-second request timeout and lacked stale-CSRF recovery; after the 60-second timeout and one-time 419 recovery, browser login redirects to `/saas` and confirms the platform admin (high, VERIFIED, 2026-11-28).
- **BUG-DATA-001** — Added/backfilled required unique `saas_tenant.uuid` in central `mountain_tourism`; browser dashboard loads and shows correct counts (high, VERIFIED, 2026-11-28).
- **BUG-ENV-004** — Nuxt dev server had a stale `[id]/edit.vue` route import; restarting `mtmtp_spa_dev` regenerated routes with `[uuid]/edit.vue`. Browser reaches the SaaS login for unauthenticated tenant-list access (medium, VERIFIED, 2026-11-28).
- **BUG-APP-012** — SaaS login page now renders before checking the existing session; browser sees the sign-in form (high, VERIFIED, 2026-11-27).
- **BUG-APP-009** — Owner dashboard `/api/reports/today` returned HTTP 500 (medium, VERIFIED, 2026-11-27). Fixed the invalid Builder call; browser refresh loaded metrics without the error banner.
- **BUG-APP-008** — Staff detail URLs use persisted UUIDs (medium, VERIFIED, 2026-11-27). Local migration and browser listing/detail/edit checks passed.
- **BUG-API-002** — Tenant validation used unsupported `Exists::connection()` calls (high, VERIFIED, 2026-11-27). Fixed and verified in item 120.
- **BUG-ENV-002** — Todo notification script failed with Windows CRLF checkout; LF policy added and SMTP delivery verified (medium, 2026-11-27).
- **BUG-API-001** — Core listing config shadowed — Unknown listing resource 404 (high, 2026-11-16)
- **BUG-APP-004** — Staff login bad credentials 403 → 401 (low, 2026-11-03)
- **BUG-APP-003** — Season overlap same-kind only (medium, 2026-11-03)
- **BUG-APP-002** — All list endpoints 500 — pagination AttributeError (high, 2026-11-03)
- **BUG-APP-001** — Prod SPA blank screen — Nuxt modules not registered (high, 2026-11-03)
- **BUG-TENANT-001** — Cross-host session identity swap risk (medium, 2026-11-02)
- **BUG-ENV-001** — Tenant DB template clone missing OPTIONS key (high, 2026-11-02)

### Closed
- **BUG-ENV-003** — Nuxt Docker HMR did not detect Windows bind-mounted edits (medium, CLOSED, 2026-11-27). Vite polling fixed it; live browser update verified under item 125.
- **BUG-MONEY-001** — Generic folio void defaults to weather reason (medium, CLOSED, 2026-11-27). Fix item 106 verified.
- **BUG-APP-007** — Staff listing table renders without column header row (medium, CLOSED, 2026-11-26).

## 3. Testing
- 2026-11-28 — Item 114 / BUG-AUTH-002: observed CSRF bootstrap at ~39s and login at ~34s, exceeding the old 30s timeout. After the 60s timeout and one-time HTTP 419 recovery, browser login redirects to `/saas` and confirms the platform admin. Initial 20s HTTP probes timed out, but later longer browser waits completed tenant creation and readiness checks below.
- 2026-11-28 — Item 114 / BUG-TENANT-002: created `copilot-qa-20261128-0655` through the SaaS UI. Tenant database, Core migrations, active Owner, and central `Complex created` activity report ready. Email status is `not_configured` (default `log` mailer); domain `copilot-qa-20261128-0655.mtmp.local` cannot resolve locally and the hosts-file write was denied. Owner domain login and email delivery are not verified; no existing tenant/database was touched.
- 2026-11-28 — Item 114 `BUG-DATA-001`: forward migration applied only to local central `mountain_tourism`; `uuid` is non-null and unique, all 1/1 tenant rows are backfilled. Browser dashboard now renders 1 total, 1 active, 0 inactive without an error. No reset; automated tests not run.
- 2026-11-28 — Item 114 browser UI check reproduced a Vite import error for removed `[id]/edit.vue`. Restarted `mtmtp_spa_dev`; the route module now imports `[uuid]/edit.vue`, SaaS login renders, and unauthenticated `/saas/tenants` redirects to login. Tenant list acceptance still needs a Super Admin session.
- 2026-11-28 — Item 114: central schema and registered `lab_local` tenant database were reset and re-provisioned; central admin, tenant registry, tenant migrations, and Owner fixture presence verified. Browser and automated tests are deferred to the user. Added DB-backed central activity coverage for tenant creation and profile changes; tests are authored but not run.
- 2026-11-27 — Item 114: added Owner retry, duplicate-email protection, readiness conflict, and no-role-overwrite API test cases. Browser now renders the SaaS sign-in screen on `saas.mtmtp.local:3000`; tenant create/login acceptance awaits a Super Admin session. Focused API tests remain unrun.
- 2026-11-27 — Item 126: eager-loaded the related Building for the Outlets list and serialized its name into the existing display cell. Regression test authored; execution could not start because `php` is not available on the Windows host. Browser verification is pending an authenticated tenant-host session.
- 2026-11-27 — Item 125: Compose config validated; `mtmtp_spa_dev` is running and `http://lab.local.mtmtp.local:3000/` returns HTTP 200. Browser rendered the tenant-host login page and applied a source edit through HMR without a manual reload or navigation. No migrations, resets, or volume removals were run.
- 2026-11-27 — Staff UUID routes: applied only the UUID migration to `lab_local`; 8 of 8 Staff rows have unique non-null UUIDs. Browser listing, detail, and edit routes passed. Regression tests were authored but not run under the code-only workflow.
- 2026-11-27 — Item 103 local preflight: API and proxied health HTTP 200; SPA login rendered. At startup, central migrations 7 and `lab_local` tenant migrations 60 were already applied; later, only the Staff UUID migration was applied after approval.
- 2026-11-27 — Owner dashboard: reproduced `/api/reports/today` HTTP 500 from `TenantSettings::on($conn)->query()`, fixed the invalid Builder call, and verified the browser dashboard loads metrics without the failure banner. No-room occupancy now shows `—` and “No rooms configured”. Regression test authored but not run.
- 2026-11-27 — Item 120 focused API flow tests: 5 passed, 17 assertions. Full `HotelModuleTest.php`: 17 passed, 2 back-to-back reservation assertions returned 409 under SQLite; PHPUnit process exits 1.
- 2026-11-27 — Item 108 focused JUnit report: 6 tests, 29 assertions, 0 failures/errors/skipped. The HTTP flow checks also passed in item 120's focused run. PHPUnit process still exits 1; project browser URL refused connection, so UI checks remain pending in TODOs 103/121.
- 2026-11-27 — item 106 focused JUnit report: 2 tests, 8 assertions, no failures/errors; test invocation returned exit code 1. Full `HotelModuleTest`: 3 passed, 8 failed due to BUG-API-002 validation errors.
- 2026-11-02 — item 06: `manage.py check` clean; migration-model check clean; new config tests code-only (suite runs on setup machine)
- 2026-11-02 — tests rewritten for DB-per-tenant / SaaS registry (not run — code-only item 11)
- 2026-11-02 — prior shared-DB isolation/auth results obsolete after tenancy change

## 4. Changes
- 2026-11-28 — `work/changes/2026-11-28--114-saas-login-csrf-recovery.md` — increased the request timeout and added Nexro-style XSRF refresh/one retry; browser SaaS login verified.
- 2026-11-28 — `work/changes/2026-11-28--114-browser-tenant-onboarding.md` — browser-created a unique tenant; database, migrations, Owner, readiness, and lifecycle event verified. Tenant-domain login and credential email remain blocked by local host resolution and mail transport configuration.
- 2026-11-28 — `work/changes/2026-11-28--114-auto-tenant-provisioning.md` — added per-tenant PostgreSQL provisioning/migrations, safe initial Owner setup, and credential-email delivery status. Existing databases are not reused or reset; browser acceptance remains pending.
- 2026-11-28 — `work/changes/2026-11-28--114-fix-saas-tenant-uuid.md` — added and applied a forward-safe central UUID migration; verified constraints, backfill, and dashboard rendering.
- 2026-11-28 — `work/changes/2026-11-28--114-central-saas-uuid-schema-mismatch.md` — traced the SaaS dashboard HTTP 500 to the missing `saas_tenant.uuid` column in the active central database; no migration/reset performed.
- 2026-11-28 — `work/changes/2026-11-28--114-stale-nuxt-route-import.md` — restarted the local Nuxt dev container, verified current UUID route imports and SaaS login rendering, and verified BUG-ENV-004.
- 2026-11-28 — `work/changes/2026-11-28--114-central-saas-db-activity.md` — clarified the central-versus-tenant DB audit boundary and added a central event for actual tenant-name changes; temporary credentials remain excluded.
- 2026-11-28 — `work/changes/2026-11-28--114-local-central-tenant-db-reset.md` — reset and re-provisioned the local central database and registered lab tenant database after explicit user approval; system `postgres` database preserved.
- 2026-11-27 — `work/changes/2026-11-27--114-saas-owner-retry-and-admin.md` — reviewed Nexro UI patterns and adapted them to the central SaaS admin, readiness/activity APIs, and safe Owner retry; sign-in screen is browser-verified, tenant create/login awaits Super Admin session.
- 2026-11-27 — `work/changes/2026-11-27--126-outlet-building-name.md` — changed the Core Outlets listing response to show the related Building name without changing the DataTable component or CRUD IDs; verification remains pending.
- 2026-11-27 — `work/changes/2026-11-27--125-docker-hmr.md` — added the Nuxt Docker development target, enabled bind-mount polling for HMR, and verified the requested tenant host without resetting data.
- 2026-11-27 — `work/changes/2026-11-27--124-nexro-agent-rules-and-skills.md` — adapted Nexro workflows to the Mountain Tourism stack and synchronized 23 skills across Cursor, Claude, and OpenCode.
- 2026-11-27 — `work/changes/2026-11-27--103-staff-uuid-routes.md` — aligned Staff URL identifiers with Nexro UUID pattern; local migration and browser route checks passed.
- 2026-11-27 — `work/changes/2026-11-27--123-dashboard-summary-api-ui.md` — fixed the owner dashboard report API and made the no-room occupancy state explicit; browser-verified.
- 2026-11-27 — `work/changes/2026-11-27--123-track-dashboard-today-report-500.md` — recorded BUG-APP-009 and moved item 123 into progress; no app fix attempted yet.
- 2026-11-27 — `work/changes/2026-11-27--103-remove-staff-detail-back-button.md` — removed the redundant back-arrow button from the Staff detail header; edit action remains.
- 2026-11-27 — `work/changes/2026-11-27--108-folio-ledger-integrity-progress.md` — completed item 108: require valid outlet attribution, protect ledger history, map nightly charges, and verify HTTP Folio/weather flows.
- 2026-11-27 — `work/changes/2026-11-27--120-tenant-validation-rules.md` — replaced unsupported tenant-scoped validation builder calls and verified active-tenant HTTP flows.
- 2026-11-27 — `work/changes/2026-11-27--122-gmail-smtp-notifications.md` — configured local Gmail SMTP, verified authentication and delivery, fixed the notifier's Windows CRLF checkout, and completed tracking.
- 2026-11-27 — `work/changes/2026-11-27--108-folio-ledger-integrity-started.md` — started item 108 after completing prerequisite item 106.
- 2026-11-27 — `work/changes/2026-11-27--107-mvp-contract-decisions.md` — recorded user-delegated choices for tenancy, map, and weather providers; formal PDF tenancy amendment remains pending.
- 2026-11-27 — `work/changes/2026-11-27--121-reconcile-browser-e2e-work-id.md` — renumbered the pending browser E2E item to avoid reusing completed item ID 76.
- 2026-11-27 — `work/changes/2026-11-27--120-track-tenant-validation-api-bug.md` — added BUG-API-002 and TODO 120 after reproducing request-validation HTTP 500s.
- 2026-11-27 — `work/changes/2026-11-27--106-folio-void-reason.md` — item 106 completed: generic voids use `other`; explicit weather preserved; focused regression report clean.
- 2026-11-26 — `work/changes/2026-11-26--114-nexro-saas-admin-ui-todo.md` — refined item 114 against Nexro's SaaS admin pattern, added MVP onboarding/readiness upgrades, and returned it to Todo.
- 2026-11-26 — `work/changes/2026-11-26--mvp-remaining-work-todos.md` — added TODO items 107-119 from the MVP scope audit.
- 2026-11-26 — `work/changes/2026-11-26--105-mvp-scope-project-audit.md` — item 105: full MVP scope audit; report at `docs/MVP_SCOPE_AUDIT.md`; BUG-MONEY-001 tracked as item 106.
- 2026-11-26 — `work/changes/2026-11-26--104-staff-listing-header.md` — item 104: Nexro-style `default_order`; fix Vuetify headers.
- 2026-11-27 — `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md` — item 103 local stack started without migrations/reset; API and SPA preflight pass, remaining browser checks in progress.
- 2026-11-23 — `work/changes/2026-11-23--mtmtp-dev-stack-restart.md` — runtime: restarted stopped mtmtp Docker stack; correct client URL `lab.local.mtmtp.local:3000`
- 2026-11-23 — `work/changes/2026-11-23--102-folio-eqms-listing-datatable.md` — item 102: Folio EQMS DataTable + module audit
- 2026-11-26 — `work/changes/2026-11-26--resolve-folio-staff-work-id-conflict.md` — preserved completed Folio item 102 and renumbered the Staff listing task to 104.
- 2026-11-16 — `work/changes/2026-11-16--101-all-modules-eqms-listing-audit.md` — item 101: all-module audit + Hotel EQMS align
- 2026-11-16 — `work/changes/2026-11-16--100-eqms-core-listing-pattern.md` — item 100: EQMS listing pattern
- 2026-11-16 — `work/changes/2026-11-16--99-core-staff-complete.md` — item 99: Staff complete + listing config fix
- 2026-11-16 — `work/changes/2026-11-16--98-core-menu-fullstack-rule.md` — item 98: fullstack rule + Core menu parity
- 2026-11-16 — `work/changes/2026-11-16--97-redesign-staff-home-dashboard.md` — item 97: staff home today desk
- 2026-11-16 — `work/changes/2026-11-16--96-core-ui-complete-settings-outlets-perms.md` — item 96: Core UI settings/outlets/`*`
- 2026-11-16 — `work/changes/2026-11-16--95-fix-spa-api-timeout-proxy-target.md` — item 95: SPA proxy → 127.0.0.1:8000
- 2026-11-14 — `work/changes/2026-11-14--94-nexro-strict-central-db-tables.md` — item 94: drop central users (Nexro-strict)
- 2026-11-14 — `work/changes/2026-11-14--central-tenant-migration-split.md` — central/tenant migration split (modules auto-discover off)
- 2026-11-14 — `work/changes/2026-11-14--local-docker-fresh-setup.md` — local Docker fresh setup (pull + rebuild + tenant login verified)
- 2026-11-14 — `work/changes/2026-11-14--93-nexro-style-mtmp-local-setup.md` — item 93: mtmp.local guide + hosts:sync
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
