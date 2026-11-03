---
title: Module 3 — hard zero-balance checkout + invoice payload
id: 43
priority: high
status: todo
milestone: 2
module: C-folio
area: backend
effort: M
dependencies: 40,42
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 3 — hard zero-balance checkout + invoice payload

## What to do (What)
Block checkout if balance != 0 (no soft bypass). Provide printable invoice JSON/payload (browser/PDF later).

Deep spec: `work/phases/Module_3_Guest_Folio_Checkout_Deep_Analysis.md`

## Current / Before
confirm_zero_balance can bypass

## Expected / After
Non-zero blocks; zero allows; invoice fields present

## Why
PDF Module C

## Acceptance Criteria (complete only when all ticked)
- [ ] Deliverable described in After is implemented (API/backend; no Nuxt unless user asks)
- [ ] Relevant automated tests added or updated (or N/A with reason if code-only run forbidden)
- [ ] `work/changes/YYYY-MM-DD--43-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/folio/`
- `backend/modules/hotel/`

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
