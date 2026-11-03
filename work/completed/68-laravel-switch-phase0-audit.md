---
title: Laravel switch — Phase 0 Django/DRF audit
id: 68
priority: high
status: completed
milestone: none
module: A-core
area: docs
effort: L
dependencies: none
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Laravel switch — Phase 0 Django/DRF audit

## What to do (What)
Master plan (`docs/` — Laravel Backend Switch) Phase 0: audit the existing Django/DRF backend
before any Laravel coding. Non-destructive. Django stays intact until Laravel cutover acceptance.

Deliverables per master plan §5:
- Existing module list (Django projects + apps)
- API inventory (endpoint, method, auth, permission, request/response, side effects, Laravel target, status)
- Database schema inventory (tables, keys, constraints, enums, soft-delete, audit fields)
- Authentication/permission matrix
- External integration list (MET Norway etc.)
- File-storage inventory
- Known issues and technical debt
- Migration risks and dependencies

## Current / Before
Backend = Django + DRF in `backend/` (modules: core, saas, hotel, folio, pos, rental, services,
transport, weather, reports; tenancy layer DB-per-tenant). No Laravel code exists.

## Expected / After
Audit docs recorded under `docs/` and referenced decisions recorded in `docs/DECISIONS.md`.
API inventory table ready to drive Laravel porting (one row per endpoint).

## Why
Master plan migration principle 1: "Audit before coding. Do not infer functionality from folder names."

## Acceptance Criteria (complete only when all ticked)
- [ ] Repository audit documented (apps, serializers, viewsets, routers, permissions, middleware)
- [ ] API inventory table complete for all existing endpoints
- [ ] Database schema inventory complete
- [ ] Auth/permission matrix complete
- [ ] Integrations + storage inventory complete
- [ ] Risks + tech debt documented
- [ ] Decisions recorded in docs/DECISIONS.md
- [ ] No destructive action taken; Django backend untouched
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated


## Completion Log

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Phase 0 audit of Django/DRF backend per Laravel master plan. Deliverables:
  `docs/LARAVEL_SWITCH_AUDIT.md` (module list, 45-endpoint API inventory with auth/permission
  mapping table, full DB schema inventory across central + 27-tenant-table model, auth &
  permission matrix, integration/storage inventories, 10 tech-debt items, 8 migration risks)
  and `docs/LARAVEL_SWITCH_MASTER_PLAN.md` (client master plan saved to repo, §22 open
  decisions table updated: Laravel confirmed, /api/v1 + money-units conflicts flagged).
  `docs/DECISIONS.md`: 2 accepted rows appended (Laravel switch supersedes Django row; port
  preserves current contracts). No destructive action; Django untouched.
- **Files changed**: docs/LARAVEL_SWITCH_AUDIT.md (new), docs/LARAVEL_SWITCH_MASTER_PLAN.md (new),
  docs/DECISIONS.md (2 rows appends), work item + tracker files.
- **Verification**: Audit facts extracted via deep code exploration (config, tenancy, all 10
  modules' models/views/permissions, common responses, tests, workflows). No tests run (doc
  work); no Docker/SSH/deploy performed.
- **Change report**: work/changes/2026-11-03--68-laravel-switch-phase0-audit.md
- **PLAN / SUMMARY / MEMORY**: updated
