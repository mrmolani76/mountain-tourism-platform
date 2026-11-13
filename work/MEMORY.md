# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Product/API code only** (Laravel + Nuxt). No Docker/VPS/CI/env/setup unless user asks.
- **Tenancy = DB-per-tenant**; Host/`Domain` resolve. No SSO.
- **79 in progress** — Module E Rental via artisan. Modules: Saas, Core, Hotel, Folio, Pos, Rental.
- New modules: always `php artisan module:make <Name> --api` (never hand folders). One migration per table.

## Next suggested pickups (kanban order)
1. Finish **79** Rental
2. Module F services
3. **72** verify on setup machine when user allows

Notifications: every todo completion mails anhali736@gmail.com (`scripts/todo-notify.sh`; creds in gitignored `scripts/.notify.env`).

## Recently completed (this week)
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
- (none)

## Do not forget
- Folder = status; WIP = 1
- Isolation = active tenant DB connection, not `tenant_id` filter
- Money append-only; DRF ViewSets + services
- Commit/push only after user says yes
