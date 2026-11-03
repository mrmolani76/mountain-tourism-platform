# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Backend / API only** (Django + DRF). No Nuxt until user says so.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/` (Complere-style). No SSO.
- Phase 1: next todos **06**, **07**.
- **Docker dev stack done (item 24)**: `./start.sh` / `./stop.sh` / `./status.sh`; SPA :3000 → `/api` → `web:8000`; db :5433; API :8010. Guide: `docs/DOCKER_SETUP.md`. Inventory stack untouched.
- **BUG-APP-001 fixed (item 25)**: `nuxt.config.ts` now registers `@vueuse/nuxt`, `@nuxtjs/i18n`, `@nuxtjs/device`, `@pinia/nuxt` + `i18n` config; login page renders, no console errors.
- **E2E staff login done (item 26)**: tenant `lab` at `http://lab.local.mountain.local:3000/` (hosts entry), DB `lab_local`, owner `owner@lab.local` (password printed once, not stored). Login + /auth/me/ verified 200 via SPA proxy.

## Blocked / waiting on user
- (none)

## Recently completed (this week)
- **26** — Tenant dev setup; staff login E2E verified
- **25** — BUG-APP-001 prod SPA blank screen fix
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
