---
title: Module F — Hotel services and activities (Laravel)
id: 80
priority: high
status: completed
milestone: 3
module: F-services
area: backend
effort: L
dependencies: 76
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Module F — Hotel services and activities (Laravel)

## Acceptance Criteria

- [x] Artisan scaffold + provider
- [x] One migration per `service_*` table
- [x] Service CRUD + schedule rules + slots
- [x] Capacity guard + staff overlap guard
- [x] Booking (room folio or walk-in) + cancel voids charge
- [x] Outing open/close; cannot close while participant not back
- [x] Code-only; change report + PLAN/SUMMARY

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-13
- **What was done**: Artisan Services module + Module F APIs (slots, bookings, outings)
- **Files changed**: `Modules/Services/**`, providers, DECISIONS, kanban
- **Verification**: route:list 16 services routes; migrate deferred
- **Change report**: `work/changes/2026-11-13--80-laravel-services-module-f.md`
- **PLAN / SUMMARY**: yes
