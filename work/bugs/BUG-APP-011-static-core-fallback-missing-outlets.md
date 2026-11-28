---
id: BUG-APP-011
title: Static Core menu fallback omits Outlets
status: BUG_CREATED
type: APP
severity: medium
created: 2026-11-27
---

# BUG-APP-011: Static Core Menu Fallback Omits Outlets

## 1. Summary
When the Core menu API is unavailable, the static fallback menu omits Outlets even though the server menu and Outlets page include it.

## 2. Environment
Nuxt frontend fallback navigation. Confirmed by comparing the frontend fallback with the Laravel Core menu configuration.

## 3. Steps to Reproduce
1. Make `GET /api/core/menus` unavailable or return an unusable response.
2. Load the staff application navigation.
3. Open the Core menu.

## 4. Expected
The fallback menu contains the same Core destinations as the server menu, including Outlets.

## 5. Actual
The fallback menu shows Staff, Buildings, Seasons, and Settings, but not Outlets.

## 6. Evidence
- `frontend/composables/core/useCoreService.ts` falls back to `appNavigation`.
- `frontend/utils/app-modules.ts` defines four `coreNavigation` entries and omits Outlets.
- `backend-laravel/Modules/Core/config/menu.php` defines Outlets as a Core submenu.
- `frontend/pages/outlets.vue` provides the `/outlets` page.

## 7. Root Cause
The frontend static fallback Core navigation does not mirror the server-side Core menu configuration.

## 8. Workaround
Restore access to the Core menu API; the API menu already includes Outlets.

## 9. Suggested Fix
Add the Outlets link to the static Core fallback navigation and keep the generic DataTable unchanged.

## 10. Impact
Owners temporarily lose the Outlets navigation entry when the menu API cannot be reached.

## 11. Related
- Work item: `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- Change report: Pending
- Other bugs: None
