---
title: Rental frontend — types, items, rent out, return
id: 85
priority: high
status: completed
milestone: 3
module: E-rental
area: frontend
effort: M
dependencies: 79
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Rental frontend — Module E

## What to do (What)

Wire Nuxt staff UI for Laravel Rental APIs (item 79): equipment types/items,
season prices, rent out, currently out, return with late/damage, menu + nav.

## Current / Before

- Backend `api/rental*` live.
- Frontend: Core + Hotel + Folio + POS. No rental pages.

## Expected / After

- Pages under `/rental` for hub, catalog, rent-out desk, out list / return.
- `useRentalApiClient` for rental calls.
- Menu + static nav include Rental for rental desk roles.

## Why

Next frontend module after POS (user: commit + next).

## Acceptance Criteria
- [x] Hub + types/items + rent out + return
- [x] Currently-out list
- [x] Menu + app navigation updated
- [x] Change report + PLAN/SUMMARY
- [x] N/A tests (code-only frontend)

## Files Likely Affected
- `frontend/pages/rental/**`
- `frontend/composables/api-clients/useRentalApiClient.ts`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Rental Nuxt hub, catalog, rent-out, currently out/return, contracts, menu
- **Files changed**: `frontend/pages/rental/**`, `useRentalApiClient.ts`, `app-modules.ts`, `Core/config/menu.php`
- **Verification**: code-only; no npm run
- **Change report**: `work/changes/2026-11-14--85-rental-frontend-module-e.md`
- **PLAN / SUMMARY**: synced
