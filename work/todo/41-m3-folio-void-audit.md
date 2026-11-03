---
title: Module 3 — manager void + audit writers
id: 41
priority: high
status: todo
milestone: 2
module: C-folio
area: backend
effort: M
dependencies: 30,40
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 3 — manager void + audit writers

## What to do (What)
Manager-only void with reason, actor from auth user, timestamp; write AuditEvent for charge/payment/void.

Deep spec: `work/phases/Module_3_Guest_Folio_Checkout_Deep_Analysis.md`

## Current / Before
Void flags exist; audit writers missing; actor may be weak

## Expected / After
Void + audit trail matches PDF

## Why
PDF Module C + A audit

## Acceptance Criteria (complete only when all ticked)
- [ ] Deliverable described in After is implemented (API/backend; no Nuxt unless user asks)
- [ ] Relevant automated tests added or updated (or N/A with reason if code-only run forbidden)
- [ ] `work/changes/YYYY-MM-DD--41-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/folio/`
- `backend/modules/core/`

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
