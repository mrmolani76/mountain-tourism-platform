---
id: BUG-APP-007
title: Staff listing table renders rows without column header row
status: CLOSED
type: APP
severity: medium
created: 2026-11-23
---

# BUG-APP-007: Staff listing table header missing

## 1. Summary

The Core Staff listing (EQMS DataTable pattern) shows filter controls and data rows (emails, roles, Enabled badges, edit/view actions) but the column header row above the rows is not visible. Users cannot tell what each column means.

## 2. Environment

Local dev stack (`mtmtp` Docker, `lab.local.mtmtp.local:3000`), branch `main` working tree.

## 3. Steps to Reproduce

1. Open `http://lab.local.mtmtp.local:3000/login` and log in as `owner@lab.test`.
2. Open the Core Staff / Users listing.
3. Observe the table area.

## 4. Expected

A header row with column labels (e.g. Email, Role, Status, Actions) directly above the first data row, matching other EQMS-style listings.

## 5. Actual

No header row. Screenshot shows: Columns filter (`4 Columns selected`), Reset Filter, Per Page `10`, Add New, then immediately the first data row (`cashier@lab.test`, `Cashier`, `Enabled`, edit/view buttons).

## 6. Evidence

- User-provided screenshot of the Staff listing (2026-11-23).

## 7. Root Cause

`ListingConfigService` converted Nexro's `default_order` into an object-shaped `default_sort` in the API response. Vuetify `VDataTableServer` expects its `sortBy` model as an array, so it raised `find is not a function` while rendering header cells.

## 8. Workaround

None known.

## 9. Suggested Fix

Keep the Staff listing in Nexro's flat `fields` + `default_order` format and do not send object-shaped `default_sort` to the DataTable. Implemented in change report `work/changes/2026-11-26--104-staff-listing-header.md`.

## 10. Verification

- 2026-11-26: Browser confirmed Staff table shows Email, Role, Status, and Actions headers with 8 staff rows.
- 2026-11-26: Browser confirmed Core Buildings, Outlets, and Seasons tables render their headers.
- No record-changing actions were performed.

## 11. Impact

All staff listing users. No data loss, no money or tenant isolation impact.

## 12. Related

- Work item: 104 (completed)
- Change report: `work/changes/2026-11-26--104-staff-listing-header.md`
- Other bugs: BUG-API-001 (listing config shadow)
