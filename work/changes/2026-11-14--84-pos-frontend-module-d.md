# Change Report: POS frontend Module D

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 84

## 1. Summary

Shipped Nuxt staff UI for Laravel POS: hub, products, shifts, orders
(open/add/settle), board check helper, packed-lunch kitchen list, and menu.

## 2. Before

- Backend `api/pos*` existed (item 77).
- Frontend had Core + Hotel + Folio only.

## 3. After

- `usePosApiClient` for catalog/shifts/orders/board/kitchen.
- Pages under `/pos` for desk workflows.
- Menu + static nav include POS for cashier roles.
- Weather item **83** parked to todo (WIP limit 1).

## 4. Reason

User: next after Folio frontend.

## 5. Files Affected

- `frontend/composables/api-clients/usePosApiClient.ts`
- `frontend/pages/pos/**`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 84 (+ 83 return to pending)

## 6. Verification / Status

- [x] Code-only (no npm run)
- [x] Settle methods cash/card/room only via API
- [ ] Browser E2E deferred
