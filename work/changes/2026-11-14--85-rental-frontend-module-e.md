# Change Report: Rental frontend Module E

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 85

## 1. Summary

Shipped Nuxt staff UI for Laravel Rental: hub, types/items/prices,
rent out, currently out + return, contracts detail, and menu.

## 2. Before

- Backend `api/rental*` existed (item 79).
- Frontend had Core + Hotel + Folio + POS only.

## 3. After

- `useRentalApiClient` for catalog and rent/return flows.
- Pages under `/rental`.
- Menu + static nav include Rental for rental desk roles.

## 4. Reason

User: commit only + next (after POS frontend).

## 5. Files Affected

- `frontend/composables/api-clients/useRentalApiClient.ts`
- `frontend/pages/rental/**`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 85

## 6. Verification / Status

- [x] Code-only (no npm run)
- [ ] Browser E2E deferred
