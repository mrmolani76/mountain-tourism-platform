---
title: Fix missing header row in Core Staff listing
id: 102
priority: medium
status: todo
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
- [ ] Header row renders on Staff listing
- [ ] No regression in other EQMS-style listings
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- Shared listing/DataTable component and/or Staff list config (exact paths to confirm).

## Notes / Follow-ups

- No API/money/tenant changes expected. UI only.

---

## Return to Pending Log (when work stops midway)

### 2026-11-26
- **Returned by**: Copilot
- **Reason**: User asked to run the browser acceptance checklist first; the WIP limit is one.
- **Next step**: Resume the Staff listing header fix after item 103 is completed.

---

## Completion Log (when work is complete)

- **Completed by**: <Agent / Name>
- **Date**: YYYY-MM-DD
- **What was done**: short summary
- **Files changed**: ...
- **Verification**: tests/commands run + result (or N/A + why)
- **Change report**: `work/changes/YYYY-MM-DD--NN-<desc>.md`
- **PLAN / SUMMARY**: updated yes/no
