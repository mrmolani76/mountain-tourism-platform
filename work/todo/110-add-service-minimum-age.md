---
title: Add minimum age to bookable services
id: 110
priority: medium
status: todo
milestone: 3
module: F-services
area: backend
effort: M
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Add minimum age to bookable services

## What to do (What)
Add the MVP-required minimum age to service configuration and enforce it when guests are booked.

## Current / Before
The service table has no minimum-age field. Service configuration and booking flows cannot persist or validate a minimum age.

## Expected / After
Staff can configure a minimum age for a service, and bookings with participants below that age are rejected with a clear validation response.

## Why
Module F in the MVP scope requires a minimum age for each service.

## Acceptance Criteria (complete only when all ticked)
- [ ] Add a forward-only migration for the service minimum-age field.
- [ ] Expose the field in service create/update requests and API responses.
- [ ] Add UI configuration and participant-age input/validation consistent with existing service flows.
- [ ] Test allowed, under-age, and unset-age cases at the API boundary.
- [ ] `work/changes/YYYY-MM-DD--110-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Services/Database/migrations/tenant/`
- `backend-laravel/Modules/Services/App/`
- `frontend/pages/services/`
- `frontend/composables/api-clients/`
- `backend-laravel/tests/`

## Notes / Follow-ups
- Confirm whether participant age is recorded as date of birth or integer age based on the existing booking model before implementing.
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
