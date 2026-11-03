# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Product/API code only** (Django + DRF). No Docker/VPS/CI/env/setup unless user asks. Frontend deferred unless user opens it.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/`. No SSO.
- **Test suite runs now**: `docker compose -f docker-compose.dev.yml exec web python -m pytest -q` → 38 passed (first run after rewrite; scratch DBs `lab_test`/`other_test` required — CI must create + migrate them the same way).

## Next suggested pickups (kanban order)
1. **34** — Domain ViewSets auth fix (hotel…reports) — audit’s critical defect
2. Then **35→66** module-wise (WIP = 1); **SaaS tenant-create console last**

Notifications: every todo completion mails anhali736@gmail.com (`scripts/todo-notify.sh`; creds in gitignored `scripts/.notify.env`).

## Recently completed (this week)
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
