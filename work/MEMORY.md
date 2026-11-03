# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Product/API code only** (Django + DRF). No Docker/VPS/CI/env/setup unless user asks. Frontend deferred unless user opens it.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/`. No SSO.
- **Test suite runs now**: `docker compose -f docker-compose.dev.yml exec web python -m pytest -q` → 38 passed (first run after rewrite; scratch DBs `lab_test`/`other_test` required — CI must create + migrate them the same way).

## Next suggested pickups (kanban order)
1. **30** — Audit foundation (AuditEvent + AuditService) — in progress next
2. **31** — Edge authz tests (inactive tenant + SaaS 403)
3. **32** — MVP policy defaults for §19 open questions
4. **33** — Module 1 exit gate (honest DoD; frontend deferred)
5. **34** — Domain ViewSets auth fix (hotel…reports) — audit's critical defect
6. Then **35→66** module-wise (WIP = 1); **SaaS tenant-create console last**

Notifications: every todo completion mails anhali736@gmail.com (`scripts/todo-notify.sh`; creds in gitignored `scripts/.notify.env`).

## Recently completed (this week)
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
