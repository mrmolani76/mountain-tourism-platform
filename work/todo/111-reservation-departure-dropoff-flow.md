---
title: Connect reservation departures to transport drop-offs
id: 111
priority: high
status: todo
milestone: 4
module: G-transport
area: backend
effort: M
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Connect reservation departures to transport drop-offs

## What to do (What)
Complete reservation transport details and create schedulable drop-off trips from guest departure information.

## Current / Before
Reservations persist arrival mode/time/station only. `TripService::pickupFromReservation()` creates a pickup trip, and no matching departure detail or drop-off generation flow was found.

## Expected / After
Staff can record external departure details and create a drop-off trip linked to the reservation, with guest/passenger and seat information derived from the reservation.

## Why
Module G requires the reservation to record arrival and departure and create pickup or drop-off trips.

## Acceptance Criteria (complete only when all ticked)
- [ ] Add validated departure mode, time, and station fields to reservations.
- [ ] Add a drop-off-from-reservation API flow with tenant-scoped reservation lookup.
- [ ] Create the trip and passenger record transactionally, respecting vehicle/driver overlap rules.
- [ ] Add reservation UI fields and a transport desk action to plan the drop-off.
- [ ] Test missing departure, valid departure, capacity, and duplicate/overlap cases.
- [ ] `work/changes/YYYY-MM-DD--111-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Hotel/`
- `backend-laravel/Modules/Transport/App/Services/TripService.php`
- `backend-laravel/Modules/Transport/routes/api.php`
- `frontend/pages/hotel/reservations/index.vue`
- `frontend/pages/transport/`
- `backend-laravel/tests/`

## Notes / Follow-ups
- Preserve existing pickup behavior and avoid duplicate trip creation on retries.
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
