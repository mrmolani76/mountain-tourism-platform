---
title: Module H — Weather (Laravel)
id: 83
priority: high
status: completed
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

- [x] Artisan scaffold + provider (manual nwidart-style scaffold; code-only)
- [x] One migration per `weather_*` table
- [x] Weather point + limit CRUD
- [x] Forecast snapshot store + evaluate warnings → targets
- [x] Cancel for weather voids charges (`weather`)
- [x] Code-only; change report + PLAN/SUMMARY

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

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Weather module scaffold; 10 tenant migrations; point/limit CRUD; MET/Open-Meteo fetch; evaluate warnings; cancel-for-weather with Folio voids + guest notify; avalanche + lobby stubs; module enabled.
- **Files changed**: `backend-laravel/Modules/Weather/**`, `bootstrap/providers.php`, `modules_statuses.json`
- **Verification**: code-only; `php -l` clean; migrate/tests deferred
- **Change report**: `work/changes/2026-11-14--83-laravel-weather-module-h.md`
- **PLAN / SUMMARY**: synced
