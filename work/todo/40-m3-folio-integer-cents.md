---
title: Module 3 — integer minor-unit money on Charge/Payment
id: 40
priority: high
status: todo
milestone: 2
module: C-folio
area: backend
effort: M
dependencies: 34
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 3 — integer minor-unit money on Charge/Payment

## What to do (What)
Migrate folio money fields to integer minor units per PDF technical rules. Append-only preserved.

Deep spec: `work/phases/Module_3_Guest_Folio_Checkout_Deep_Analysis.md`

## Current / Before
DecimalField amounts

## Expected / After
amount_cents (or equivalent) on Charge/Payment; APIs use integers

## Why
PDF money rule

## Acceptance Criteria (complete only when all ticked)
- [ ] Deliverable described in After is implemented (API/backend; no Nuxt unless user asks)
- [ ] Relevant automated tests added or updated (or N/A with reason if code-only run forbidden)
- [ ] `work/changes/YYYY-MM-DD--40-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/folio/`

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
