# Change Report: Services frontend Module F

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 86

## 1. Summary

Shipped Nuxt staff UI for Laravel Services: catalog, schedule rules, slots,
bookings, guided outing lifecycle, and menu.

## 2. Before

- Backend `api/services*` existed (item 80).
- Frontend had Core + Hotel + Folio + POS + Rental only.

## 3. After

- `useServicesApiClient` for catalog/slots/bookings/outings.
- Pages under `/services`.
- Menu + static nav include Services for service desk roles.

## 4. Reason

User: next after Rental frontend.

## 5. Files Affected

- `frontend/composables/api-clients/useServicesApiClient.ts`
- `frontend/pages/services/**`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 86

## 6. Verification / Status

- [x] Code-only (no npm run)
- [ ] Browser E2E deferred
