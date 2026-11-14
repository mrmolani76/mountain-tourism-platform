---
title: Module H — Weather (Laravel)
id: 83
priority: high
status: todo
milestone: 4
module: H-weather
area: backend
effort: L
dependencies: 81
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Module H — Weather (Laravel)

## What to do (What)

Port PDF Module H Phase 4 core into `backend-laravel/Modules/Weather` via
`php artisan module:make Weather --api`. Schema §11. Weather points, MET cache
snapshot, limits, warnings on lifts/trips/slots, explicit cancel-for-weather
voids (`reason_code=weather`). Avalanche/lobby APIs stub OK; UI Phase 5.

## Current / Before

- No Weather module on Laravel.
- Transport lifts/trips exist for warning targets.

## Expected / After

- One migration per `weather_*` table.
- Point/limit CRUD; evaluate warnings from forecast values.
- Cancel-for-weather voids related folio charges; guest notify list payload.
- Server-side MET fetch service (no browser MET calls).

## Why

Phase 4 next after Module G. PDF: warnings appear when limits cross; weather
cancellation leaves folios correct.

## Acceptance Criteria (complete only when all ticked)

- [ ] Artisan scaffold + provider
- [ ] One migration per `weather_*` table
- [ ] Weather point + limit CRUD
- [ ] Forecast snapshot store + evaluate warnings → targets
- [ ] Cancel for weather voids charges (`weather`)
- [ ] Code-only; change report + PLAN/SUMMARY

## Files Likely Affected

- `backend-laravel/Modules/Weather/**`
- `backend-laravel/bootstrap/providers.php`

## Notes / Follow-ups

- No new Composer deps; use Laravel HTTP client for MET.
- Lobby TV page + avalanche staff UI deferred to Phase 5.

---

## Return to Pending Log (when work stops midway)

### 2026-11-14
- **Returned by**: Agent
- **Reason**: User asked next frontend module (POS); WIP limit 1
- **Next step**: Resume Module H Weather after POS frontend

## Completion Log

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
