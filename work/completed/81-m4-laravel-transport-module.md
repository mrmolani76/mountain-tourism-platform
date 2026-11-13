---
title: Module G — Transport and lifts (Laravel)
id: 81
priority: high
status: completed
milestone: 4
module: G-transport
area: backend
effort: L
dependencies: 76
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Module G — Transport and lifts (Laravel)

## What to do (What)

Port PDF Module G into `backend-laravel/Modules/Transport` via
`php artisan module:make Transport --api`. Schema §10. Vehicles, drivers, routes,
trips (overlap + seat guards), lift status, lift pass sales via Folio.
Pickup from reservation arrival; passenger list JSON.

## Acceptance Criteria

- [x] Artisan scaffold + provider
- [x] One migration per `transport_*` table
- [x] Vehicle/driver/route CRUD
- [x] Trip create with driver/vehicle overlap + seat booking
- [x] Pickup from reservation arrival
- [x] Lift status change with history; lobby status API
- [x] Lift pass sale → folio charge
- [x] Code-only; change report + PLAN/SUMMARY

---


## Return to Pending Log (when work stops midway)

### 2026-11-13
- **Returned by**: Agent
- **Reason**: User redirected to frontend remaining work for completed modules
- **Next step**: Finish Module G verification (routes/services already largely present) after Folio frontend item

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-13
- **What was done**: Artisan Transport module; 10 tenant migrations; vehicle/driver/route CRUD; trips with overlap + seats; reservation pickup; passenger list; lift status history + lobby API; lift pass sale → folio charge.
- **Files changed**: `backend-laravel/Modules/Transport/**`, `bootstrap/providers.php`, work kanban 81, change report.
- **Verification**: `route:list --path=api/transport` → 25 routes; php -l clean. migrate/tests deferred (code-only).
- **Change report**: `work/changes/2026-11-13--81-laravel-transport-module-g.md`
- **PLAN / SUMMARY**: synced
