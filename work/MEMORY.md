# MEMORY — session handoff

> New session: read this + `work/PLAN.md` + `work/SUMMARY.md` + `CLAUDE.md` header callouts.
> Keep English in this file. Short bullets only.

## Current focus
- **Backend / API only** (Django + DRF). No Nuxt until user says so.
- **Tenancy = DB-per-tenant**; domain in `backend/modules/` (Complere-style). No SSO.
- Phase 1: next todos **06**, **07**.
- Setup (createdb / migrate tenants / hosts) still pending.

## Blocked / waiting on user
- (none)

## Recently completed (this week)
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
