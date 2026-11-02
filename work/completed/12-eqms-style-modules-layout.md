---
title: EQMS-style modules layout
id: 12
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 11
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# EQMS-style modules layout

## What to do (What)
Restructure `backend/apps/` → `backend/modules/` with Complere-like per-module folders (models, services, serializers, views, migrations, urls, module.json). Code only — no setup.

## Current / Before
Domain code under flat `backend/apps/{saas,core,hotel,...}`.

## Expected / After
`backend/modules/{saas,core,hotel,folio,pos,rental,services,transport,weather,reports}/` each with EQMS-mirror internal layout; imports `modules.*`; docs updated.

## Why
User: module-wise like eQMS Complere.

## Acceptance Criteria (complete only when all ticked)
- [x] `modules/` replaces `apps/` for domain code
- [x] Each module has shared internal layout + module.json
- [x] INSTALLED_APPS / imports / tests updated
- [x] Change report + PLAN/SUMMARY

## Files Likely Affected
- `backend/apps/` → `backend/modules/`
- `backend/config/`, `backend/tests/`, docs

## Notes / Follow-ups
- `tenancy/` stays platform (like Complere `app/`)

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Moved domain code to `backend/modules/` with Complere-style per-module layout; `services_app` → `services`; imports `modules.*`; docs/skills updated.
- **Files changed**: see change report
- **Verification**: N/A — code only, no setup/test run
- **Change report**: `work/changes/2026-11-02--12-eqms-style-modules-layout.md`
- **PLAN / SUMMARY**: updated yes
