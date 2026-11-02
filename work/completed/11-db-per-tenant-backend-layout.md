---
title: DB-per-tenant backend layout (DRF)
id: 11
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: L
dependencies: 05
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# DB-per-tenant backend layout (DRF)

## What to do (What)
EQMS-style database-per-tenant backend folder layout: `tenancy/`, `apps.saas` (central), domain apps stubs, Host resolve + DB router. Auth stays email/password session. No SSO. DRF ViewSets + routers. Code only — no DB/provision/VPS setup runs.

## Current / Before
Shared DB + `tenant_id` + `TenantManager` in `apps.core`. Function-based auth/staff views.

## Expected / After
- Central models in `apps.saas`; tenant business models without `tenant_id`
- `tenancy/` middleware + router + connection helpers + provision command stubs
- Domain app stubs: hotel, folio, pos, rental, services_app, transport, weather, reports
- DRF ViewSets/APIViews + DefaultRouter
- Docs decisions updated; isolation tests rewritten for DB-per-tenant
- No migrate/createdb executed in this item

## Why
User: each tenant own DB; backend structure like EQMS; frontend deferred; SSO out; DRF-aligned; code only.

## Acceptance Criteria (complete only when all ticked)
- [x] Folder layout + DRF routers/viewsets in place
- [x] Docs DECISIONS/ARCHITECTURE/TECHNICAL_RULES/CLAUDE tenancy updated
- [x] Tests rewritten (not run — code-only)
- [x] `work/changes/YYYY-MM-DD--11-*.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/tenancy/`
- `backend/apps/saas/`
- `backend/apps/core/`
- `backend/apps/{hotel,folio,pos,rental,services_app,transport,weather,reports}/`
- `backend/config/`
- `backend/tests/`
- `docs/*`, `CLAUDE.md`

## Notes / Follow-ups
- Setup (createdb, migrate, hosts) = later work
- Item 07 becomes provision-oriented after this

---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: DB-per-tenant backend layout with DRF ViewSets/routers; central saas + tenancy platform + domain stubs; stripped tenant_id; SSO out; code only (no setup run).
- **Files changed**: see `work/changes/2026-11-02--11-db-per-tenant-backend-layout.md`
- **Verification**: N/A — user requested code only, no migrate/test run
- **Change report**: `work/changes/2026-11-02--11-db-per-tenant-backend-layout.md`
- **PLAN / SUMMARY**: updated yes
