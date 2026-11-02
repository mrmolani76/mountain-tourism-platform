---
title: Architecture note + core data model draft
id: 02
priority: high
status: todo
milestone: 1
module: A-core
area: docs
effort: M
dependencies: none
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Architecture note + core data model draft

## What to do (What)
Write/update `docs/ARCHITECTURE.md` for client review: API-first Django+DRF layout, tenancy approach, core entities from PDF glossary. Draft model list (Tenant, Building, Outlet, Season, Staff/Role, settings). Backend only.

## Current / Before
`docs/ARCHITECTURE.md` has placeholders and open questions; no formal data model draft tied to Phase 1.

## Expected / After
Short architecture note + entity list/relationships ready for review; open questions resolved or explicitly listed. No frontend app.

## Why
PDF Milestone 1 requires data model and architecture note before/with foundation build.

## Acceptance Criteria (complete only when all ticked)
- [ ] `docs/ARCHITECTURE.md` filled for backend/API
- [ ] Core entities named per `docs/GLOSSARY.md`
- [ ] Tenant isolation approach chosen (scoped manager vs RLS) documented
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `docs/ARCHITECTURE.md`
- `docs/DECISIONS.md` (if isolation choice recorded)

## Notes / Follow-ups
- Frontend deferred; API is the contract.
