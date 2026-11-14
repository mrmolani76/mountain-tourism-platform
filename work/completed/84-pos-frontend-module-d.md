---
title: POS frontend — catalog, shifts, orders, settle
id: 84
priority: high
status: completed
milestone: 3
module: D-pos
area: frontend
effort: M
dependencies: 77
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# POS frontend — Module D

## What to do (What)

Wire Nuxt staff UI for Laravel POS APIs (item 77): products/categories,
cashier shifts, open order / add items / settle (cash/card/room), board check,
menu + nav.

## Current / Before

- Backend `api/pos*` live.
- Frontend: Core + Hotel + Folio only. No POS pages.

## Expected / After

- Pages under `/pos` for hub, products, shifts, orders desk.
- `usePosApiClient` for POS calls.
- Menu + static nav include POS for cashier roles.

## Why

Next frontend module after Folio (user: next).

## Acceptance Criteria
- [x] Hub + products + shifts + orders (open/add/settle)
- [x] Board check helper on order desk
- [x] Menu + app navigation updated
- [x] Change report + PLAN/SUMMARY
- [x] N/A tests (code-only frontend)

## Files Likely Affected
- `frontend/pages/pos/**`
- `frontend/composables/api-clients/usePosApiClient.ts`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: POS Nuxt hub, products, shifts, orders desk (add/settle), board check, packed lunches, menu
- **Files changed**: `frontend/pages/pos/**`, `usePosApiClient.ts`, `app-modules.ts`, `Core/config/menu.php`
- **Verification**: code-only; no npm run
- **Change report**: `work/changes/2026-11-14--84-pos-frontend-module-d.md`
- **PLAN / SUMMARY**: updated yes
