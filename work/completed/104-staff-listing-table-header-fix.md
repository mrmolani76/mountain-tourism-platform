---
title: Fix missing header row in Core Staff listing
id: 104
priority: medium
status: completed
milestone: none
module: A-core
area: frontend
effort: S
dependencies: none
bug: BUG-APP-007
created: 2026-11-23
updated: 2026-11-26
---

# Fix missing header row in Core Staff listing

## What to do (What)

Restore the column header row in the Core Staff listing so users can read column labels above the data rows. Shared EQMS DataTable pattern.

## Current / Before

`http://lab.local.mtmtp.local:3000/` Staff listing shows filters and 8 staff rows, but no column header row. See `work/bugs/BUG-APP-007-staff-listing-table-header-missing.md`.

## Expected / After

Header row with labels (Email, Role, Status, Actions) renders above the first data row, consistent with other EQMS-style listings.

## Why

Confirmed UI bug reported by user on 2026-11-23. Listing is unusable without column labels.

## Acceptance Criteria (complete only when all ticked)
- [x] Header row renders on Staff listing
- [x] No regression in Core Buildings, Outlets, and Seasons listings
- [x] Change report written
- [x] PLAN.md + SUMMARY.md updated

## Files Affected

- `backend-laravel/Modules/Core/config/listing.php`
- `backend-laravel/Modules/Core/App/Services/ListingConfigService.php`
- `frontend/types/data-table-config.ts`

## Notes / Follow-ups

- The API keeps Nexro's `default_order` format. It no longer serializes an object as Vuetify's `sortBy` model.

---

## Return to Pending Log (when work stops midway)

### 2026-11-26
- **Returned by**: Copilot
- **Reason**: User asked to run the browser acceptance checklist first; the WIP limit is one.
- **Next step**: Resume the Staff listing header fix after item 103 is completed.

---

## Completion Log

- **Completed by**: Copilot
- **Date**: 2026-11-26
- **What was done**: Aligned Staff list config with Nexro `fields` + `default_order`; stopped returning object-shaped `default_sort` to Vuetify.
- **Files changed**: Core listing config, `ListingConfigService`, DataTable config type, bug/work tracking docs.
- **Verification**: Browser check confirmed Staff headers Email, Role, Status, Actions with 8 rows. Buildings, Outlets, and Seasons also rendered their configured header rows.
- **Change report**: `work/changes/2026-11-26--104-staff-listing-header.md`
- **PLAN / SUMMARY**: updated yes
