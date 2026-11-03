# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Product/API code only** (Django + DRF). No Docker/VPS/CI/env/setup unless user asks. Frontend deferred unless user opens it.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/`. No SSO.
- **Latest audit**: `work/audits/2026-11-03-project-audit.md` (item 27). Pilot not ready.
- Critical finding: domain ViewSets (hotel…reports) use `authentication_classes = []` — fix next as bug + coding item.

## Blocked / waiting on user
- (none)

## Recently completed (this week)
- **27** — Full project audit report (2026-11-03)
- **26** — Tenant dev setup; staff login E2E verified
- **25** — BUG-APP-001 prod SPA blank screen fix
- **24** — Docker dev setup (inventory-style), verified
- **23** — Frontend SPA copy + Django wiring
- **18–22** — DRF settings + folio/hotel/pos + remaining modules CRUD
- **01–17** — foundation, tenancy, auth, core config, docs

## Next suggested pickups
1. Bug + fix: restore `StaffSessionAuthentication` on domain ViewSets
2. Module B/C depth: check-in → Folio, nightly posting, integer money, void actor = user
3. Tests: folio void/checkout + board posting + keep isolation green
4. Later: Reservation/HutBed, POS charge-to-folio, rental conflict, lifts, weather voids
5. Frontend desks only when user asks

## Do not forget
- Folder = status; WIP = 1
- Isolation = active tenant DB connection, not `tenant_id` filter
- Money append-only; DRF ViewSets + services
- Commit/push only after user says yes
