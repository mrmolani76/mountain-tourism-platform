---
title: Protect folio ledger integrity and outlet attribution
id: 108
priority: high
status: todo
milestone: 2
module: C-folio
area: backend
effort: L
dependencies: 106
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Protect folio ledger integrity and outlet attribution

## What to do (What)
Make required outlet attribution and append-only charge/payment history enforceable across folio creation, nightly posting, and deletion paths.

## Current / Before
`folio_charge.outlet_id` is nullable; charge requests permit null and hotel nightly posting does not supply an outlet. Charge and payment rows also cascade when their Folio is deleted.

## Expected / After
Every charge has a valid tenant outlet attribution, and deleting a Folio cannot delete its charge/payment ledger history. Tests cover relevant cross-module charge sources and protected deletion behavior.

## Why
The MVP requires every charge to record its outlet. Project rules require charges and payments to remain append-only.

## Acceptance Criteria (complete only when all ticked)
- [ ] Define how room-night and board charges are attributed to an outlet, including legacy/null cases.
- [ ] Reject or safely handle charge writes without valid outlet attribution.
- [ ] Change the Folio deletion relationship so money history cannot be cascaded away.
- [ ] Add regression coverage for outlet attribution and deletion protection.
- [ ] Confirm existing weather cancellation and room-charge flows still post and void correctly.
- [ ] `work/changes/YYYY-MM-DD--108-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Folio/Database/migrations/tenant/`
- `backend-laravel/Modules/Folio/App/Http/Requests/`
- `backend-laravel/Modules/Folio/App/Services/`
- `backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`

## Notes / Follow-ups
- Add a new migration; never edit a migration that may have shipped.
- Depends on item 106 to avoid overlapping void-reason work.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

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
