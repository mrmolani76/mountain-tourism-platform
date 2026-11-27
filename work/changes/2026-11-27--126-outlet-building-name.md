# Change Report: Show Building Names in the Core Outlets Listing

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 126

## 1. Summary
Updated the Core Outlets listing response to show the related Building name instead of its numeric ID.

## 2. Before
- The listing config used `building_id` as the Building column key.
- The Core listing serializer returned the numeric ID as plain text, matching the user's screenshot.

## 3. After
- The Outlets list query eager-loads `building:id,name`.
- The existing `building_id` display cell receives the related Building name as its label.
- Outlet create/edit forms and CRUD responses still use `building_id`.
- The generic DataTable component is unchanged.

## 4. Reason
The user clarified that the Outlets screen should show a name instead of an ID and explicitly said not to change the DataTable.

## 5. Files Affected
- `backend-laravel/Modules/Core/App/Http/Controllers/CoreListingController.php`
- `backend-laravel/tests/Feature/Modules/Core/BuildingOutletSeasonTest.php`
- `work/inprogress/126-show-building-names-in-outlets.md`
- `work/bugs/BUG-APP-010-outlets-list-shows-building-id.md`
- `work/bugs/INDEX.md`
- `work/PLAN.md`
- `work/SUMMARY.md`

## 6. Verification / Status
- [x] `git diff --check` passed for the implementation and regression test.
- [x] Focused API regression test authored to assert the Building display label.
- [ ] Focused test command could not start because `php` is not available on the Windows host.
- [ ] Browser verification pending: the shared tenant-host page currently shows the login screen, and no credentials were provided.
- [x] No DataTable component changes.
- **Status**: Implementation applied; item remains in progress pending an authenticated browser/API verification.
