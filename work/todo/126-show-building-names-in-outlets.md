---
title: Show Building names in Core Outlets listing
id: 126
priority: medium
status: todo
milestone: none
module: A-core
area: backend
effort: S
dependencies: none
bug: BUG-APP-010
created: 2026-11-27
updated: 2026-11-27
---

# Show Building names in Core Outlets listing

## What to do (What)
Show the related Building name in the Core Outlets table instead of the numeric Building ID shown in the user's screenshot.

## Current / Before
- The Outlets listing column is configured as `building_id`.
- The listing serializer turns the numeric ID into plain text, so the table shows values such as `2`.

## Expected / After
- The Building column displays the related Building name.
- Outlet create/edit forms and CRUD payloads continue to use `building_id`.
- The listing remains tenant-scoped and does not introduce an N+1 query.

## Why
The user clarified: “id nhi name show karo isme”. Staff need to identify the Building associated with each Outlet by name.

## Acceptance Criteria (complete only when all ticked)
- [ ] Resolve the Building name in the Core Outlets listing without changing the Outlet CRUD contract or the DataTable component.
- [ ] Verify the Building column displays a name rather than a numeric ID in the browser or a focused API check.
- [ ] Preserve tenant scoping and avoid per-row Building queries.
- [ ] `work/changes/2026-11-27--126-outlet-building-name.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Core/App/Http/Controllers/CoreListingController.php`
- `backend-laravel/Modules/Core/config/listing.php`
- `backend-laravel/tests/Feature/Modules/Core/`
- `work/PLAN.md`
- `work/SUMMARY.md`

## Notes / Follow-ups
- Keep the edit form's `building_id` select and persisted ID unchanged.
- Do not change the generic DataTable component.
- Use the tenant-host URL `http://lab.local.mtmtp.local:3000/` for browser checks.
- The regression test is authored, but the Windows host has no `php` executable; browser verification is also pending an authenticated tenant-host session.

---

## Return to Pending Log (when work stops midway)
- **Returned by**: Copilot
- **Date**: 2026-11-27
- **Reason**: The Building-name listing change is committed, but its focused regression test could not run because PHP is unavailable on the Windows host, and authenticated browser verification is still pending. The user asked to prioritize completing Module A Core.
- **Next step**: Resume after the Module A SaaS admin work, then run the focused API test or authenticated tenant-host browser check.
- **Change report**: `work/changes/2026-11-27--126-outlet-building-name.md`
---

## Completion Log (when the work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
