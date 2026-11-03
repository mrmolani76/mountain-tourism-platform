# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Backend / API only** (Django + DRF). No Nuxt until user says so.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/` (Complere-style). No SSO.
- Phase 1: next todos **06**, **07**.
- **Docker dev stack done (item 24)**: `./start.sh` / `./stop.sh` / `./status.sh`; SPA :3000 → `/api` → `web:8000`; db :5433; API :8010. Guide: `docs/DOCKER_SETUP.md`. Inventory stack untouched.

## Blocked / waiting on user
- (none)

## Recently completed (this week)
- **24** — Docker dev setup (inventory-style), verified on this machine
- **23** — Frontend SPA copy + Django wiring
- **12** — EQMS-style `modules/` layout
- **11** — DB-per-tenant backend layout (DRF), code only
- **01–05** — foundation items

## Next suggested pickups
1. **06** — Buildings, outlets, seasons APIs (tenant DB models)
2. **07** — Super admin provision (wire to registry; setup still separate)
3. Later: ops setup for createdb + migrate_tenants execute path

## Do not forget
- Folder = status; WIP = 1
- Isolation = active tenant DB connection, not `tenant_id` filter
- Money append-only; DRF ViewSets + services
- Commit/push only after user says yes
