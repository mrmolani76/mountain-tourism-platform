---
title: Document API with OpenAPI and align current project docs
id: 116
priority: high
status: todo
milestone: 5
module: docs
area: docs
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Document API with OpenAPI and align current project docs

## What to do (What)
Deliver OpenAPI documentation for the current Laravel API and reconcile project architecture/CI/schema/handover documentation with the implemented stack.

## Current / Before
No OpenAPI/Swagger specification or generator was found. `docs/CI.md`, `docs/ARCHITECTURE.md`, `docs/DATABASE_SCHEMA.md`, and `docs/MILESTONES.md` contain Django-era or deferred descriptions. `docs/README.md` links to a missing `HANDOVER.md`.

## Expected / After
The API is documented with OpenAPI without introducing an unapproved dependency. Architecture, CI, schema, milestones and handover links accurately describe the current Laravel + Nuxt product and approved contract.

## Why
The PDF requires OpenAPI (Swagger), an accurate API handover, and documentation that allows future clients to reuse the API.

## Acceptance Criteria (complete only when all ticked)
- [ ] Choose a no-new-dependency documentation approach or obtain explicit approval before adding a package.
- [ ] Document auth, tenant-host behavior, request/response envelopes, validation errors and the current route surface in OpenAPI.
- [ ] Reconcile CI and architecture docs with the Laravel + Nuxt workflows that actually exist.
- [ ] Reconcile the schema status tables with shipped migrations and explicitly list approved deviations.
- [ ] Add or remove the stale `HANDOVER.md` link and ensure every docs index link resolves.
- [ ] `work/changes/YYYY-MM-DD--116-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `docs/README.md`
- `docs/CI.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/MILESTONES.md`
- `docs/HANDOVER.md`
- `backend-laravel/`

## Notes / Follow-ups
- Do not add Composer/npm dependencies without explicit approval.
- Resolve contract discrepancies through item 107 before documenting them as accepted.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
