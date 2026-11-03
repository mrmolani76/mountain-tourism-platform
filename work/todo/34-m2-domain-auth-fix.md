---
title: Fix domain ViewSets auth (hotel…reports)
id: 34
priority: high
status: todo
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 33
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Fix domain ViewSets auth (hotel…reports)

## What to do (What)
BUG / auth: domain modules (hotel, folio, pos, rental, services, transport, weather, reports) must not use empty authentication_classes. Align with core staff auth.

Deep spec: `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`

## Current / Before
Audit: several domain ViewSets set authentication_classes = []

## Expected / After
All domain ViewSets require staff auth; unauthenticated → 401; tests cover one endpoint per module or shared base

## Why
Security / Module B–I prerequisite

## Acceptance Criteria (complete only when all ticked)
- [ ] Deliverable described in After is implemented (API/backend; no Nuxt unless user asks)
- [ ] Relevant automated tests added or updated (or N/A with reason if code-only run forbidden)
- [ ] `work/changes/YYYY-MM-DD--34-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/*/views/`
- `backend/tests/`

## Notes / Follow-ups
- Product/API code only. No Docker/VPS/CI/setup unless user asks.
- WIP = 1. Pickup before edits.
- Frontend screens deferred.

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
