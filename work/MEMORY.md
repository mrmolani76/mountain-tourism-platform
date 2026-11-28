# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **126** Outlets Building-name display is implemented and committed; focused test and authenticated browser verification remain pending under `work/todo/`.
- **105** Full MVP PDF versus project audit completed at `docs/MVP_SCOPE_AUDIT.md`. Treat PDF as contract and mark unverified work honestly.
- **123** Dashboard `/api/reports/today` HTTP 500 fixed and browser-verified (BUG-APP-009). Regression test authored but not run under the code-only workflow.
- **103** Manual browser acceptance is next in `work/todo/`; Staff UUID migration is applied to `lab_local`, and listing/detail/edit routes passed. Broader auth and module checks remain.
- **104** Staff listing header bug is fixed and browser-verified using Nexro's `fields` + `default_order` listing pattern.
- **Fullstack product code** (Laravel API + Nuxt UI together). No Docker/VPS/CI/env/setup unless user asks.
- **Tenancy = DB-per-tenant**; Host/`Domain` resolve. Prefer local `{slug}.mtmp.local` (`docs/LOCAL_MTMP_SETUP.md`).
- **WIP limit = 1**. Item **114** is active for Nexro-style temporary Owner onboarding and DB-backed SaaS lifecycle logs. Central `mountain_tourism` and `lab_local` were reset with approval on 2026-11-28; `postgres` was preserved. Require explicit authorization before future resets. Item **90** MySQL switch remains pending.
- New modules: always `php artisan module:make <Name> --api`. One migration per table.

## Next suggested pickups (kanban order)
1. **109** Schedule tenant-local nightly and weather jobs.
2. **110** Add minimum age to bookable services.
3. **111** Connect reservation departures to transport drop-offs.

Notifications: every todo completion mails anhali736@gmail.com (`scripts/todo-notify.sh`; creds in gitignored `scripts/.notify.env`).

## Recently completed (this week)
- **125** — Started the Nuxt development server in Docker, enabled bind-mount polling, and verified live HMR at `http://lab.local.mtmtp.local:3000/` without migrations, resets, or volume removal (medium, 2026-11-27)
- **124** — Adapted and synchronized Nexro-inspired rules and 12 new skills across Cursor, Claude, and OpenCode (high, 2026-11-27)
- **123** — Fixed dashboard today-report HTTP 500, added API regression coverage, clarified empty-room occupancy UI, and verified the dashboard in the browser (medium, 2026-11-27)
- **108** — Protected Folio ledger and Outlet attribution; HTTP charge-to-room and weather-cancellation flows verified (high, 2026-11-27)
- **120** — Replaced unsupported tenant validation builder calls and verified active-tenant HTTP flows (high, 2026-11-27)
- **122** — Configured Gmail SMTP notifier, fixed Windows LF handling, and verified delivery (medium, 2026-11-27)
- **107** — Contract choices recorded: DB-per-tenant retained, OSM maps, MET Norway only; formal PDF tenancy amendment still required (high, 2026-11-27)
- **106** — Generic folio voids default to `other`; explicit weather remains. Focused JUnit report clean; full Hotel file has unrelated BUG-API-002 validation failures (high, 2026-11-27)
- **105** — Full MVP scope audit, module and relationship mapping (high, 2026-11-26)
- **104** — Fix Staff listing header row with Nexro `default_order` configuration (medium, 2026-11-26)
- **93** — Nexro-style mtmp.local guide + hosts:sync (high, 2026-11-14)
- **92** — EQMS/Nexro tenancy routes + middleware (high, 2026-11-14)
- **84** — POS frontend Module D (high, 2026-11-14)
- **82** — Folio frontend Module C (high, 2026-11-13)
- **80** — Module F Services Laravel via artisan + APIs (high, 2026-11-13)
- **79** — Module E Rental Laravel via artisan + APIs (high, 2026-11-13)
- **78** — Split Pos migrations one table per file (high, 2026-11-13)
- **77** — Module D POS Laravel via artisan + APIs (high, 2026-11-13)
- **76** — Module 3 Folio Laravel (charge-to-room, list, invoice JSON) (high, 2026-11-13)
- **75** — EQMS DataTable listing align Hotel/Core (high, 2026-11-06)
- **74** — EQMS menu + staff CRUD + Hotel DataTable listings (high, 2026-11-06)
- **73** — Full platform DB schema design Modules A–I (high, 2026-11-05)
- **69** — Laravel foundation Stage B complete (app+pgsql+envelope+health+CI) (high, 2026-11-03)
- **68** — Laravel switch Phase 0 Django/DRF audit (45 endpoints, schema, auth matrix, risks) (high, 2026-11-03) — Laravel switch Phase 0 Django/DRF audit (45 endpoints, schema, auth matrix, risks) (high, 2026-11-03)
- **34** — Fix domain ViewSets auth (hotel…reports) — StaffSessionAuthentication on all 20 domain ViewSets; BUG-APP-005 resolved; suite 46 passed (high, 2026-11-03)
- **33** — Module 1 exit gate —§20 DoD review; backend/API MVP skeleton CLOSED (audit writers + frontend deferred) (high, 2026-11-03)
- **32** — Module 1 MVP policy defaults (§19 Q3–Q18) — docs/DECISIONS.md row; Q19 resolved (high, 2026-11-03)
- **31** — Module 1 edge authz tests — deactivated tenant login 404 + staff→SaaS 403; full suite 40 passed (high, 2026-11-03)
- **30** — Module 1 audit foundation — AuditEvent + AuditService.record(), migration `0006_add_audit_event`, `manage.py check` clean (high, 2026-11-03)
- **29** — Module 1 tenancy decision frozen (DB-per-tenant + Host/Domain; docs-only)
- **28** — Module 1 verification; first full test run, 38 green; BUG-APP-002/003/004 (pagination 500, season cross-kind overlap, login 401)
- **27** — Full project audit report (2026-11-03)
- **26** — Tenant dev setup; staff login E2E verified
- **25** — BUG-APP-001 prod SPA blank screen fix
- **24** — Docker dev setup (inventory-style), verified
- **23** — Frontend SPA copy + Django wiring
- **18–22** — DRF settings + folio/hotel/pos + remaining modules CRUD
- **01–17** — foundation, tenancy, auth, core config, docs

## Blocked / waiting on user
- **114** — Compare Nexro's temporary Owner credentials/first-login flow and implement the requested matching behavior. User will browser-test on the freshly reset local setup.

## Do not forget
- Folder = status; WIP = 1
- Isolation = active tenant DB connection, not `tenant_id` filter
- Money append-only; DRF ViewSets + services
- Commit/push only after user says yes
- 2026-11-13: Completed **81** Module G Transport (Laravel). Next: H Weather or 72 verify.
- 2026-11-14: Completed **85** Rental frontend. Next: Services frontend or Weather 83.
- 2026-11-14: Completed **86** Services frontend. Next: Transport frontend or Weather 83.
- 2026-11-14: Completed **87** Transport frontend. Next: Weather 83.
