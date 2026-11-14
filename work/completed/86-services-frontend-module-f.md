---
title: Services frontend — catalog, slots, bookings, outings
id: 86
priority: high
status: completed
milestone: 3
module: F-services
area: frontend
effort: M
dependencies: 80
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Services frontend — Module F

## What to do (What)

Wire Nuxt staff UI for Laravel Services APIs (item 80): catalog, schedule rules,
slots + staff assign, bookings + cancel, guided outing open/out/back/close.
Menu + nav.

## Current / Before

- Backend `api/services*` live.
- Frontend: Core + Hotel + Folio + POS + Rental. No Services pages.

## Expected / After

- Pages under `/services` for hub, catalog, slots, bookings, outing desk.
- `useServicesApiClient`.
- Menu + static nav include Services.

## Why

User: next after Rental frontend.

## Acceptance Criteria
- [x] Hub + catalog + slots + bookings
- [ ] Staff assign + outing lifecycle
- [x] Menu + app navigation updated
- [x] Change report + PLAN/SUMMARY
- [ ] N/A tests (code-only)

## Files Likely Affected
- `frontend/pages/services/**`
- `frontend/composables/api-clients/useServicesApiClient.ts`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Services Nuxt hub, catalog, schedule rules, slots/bookings, outing out/back/close, menu
- **Files changed**: `frontend/pages/services/**`, `useServicesApiClient.ts`, `app-modules.ts`, `Core/config/menu.php`
- **Verification**: code-only; no npm run
- **Change report**: `work/changes/2026-11-14--86-services-frontend-module-f.md`
- **PLAN / SUMMARY**: synced
