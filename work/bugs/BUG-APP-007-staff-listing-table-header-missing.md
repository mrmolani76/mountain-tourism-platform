---
id: BUG-APP-007
title: Staff listing table renders rows without column header row
status: BUG_CREATED
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

Under investigation. Code inspection of the shared listing component and the Staff list config is in progress.

## 8. Workaround

None known.

## 9. Suggested Fix

Pending code inspection. Likely in the shared DataTable component header render path or the Staff list config, not in the API.

## 10. Impact

All staff listing users. No data loss, no money or tenant isolation impact.

## 11. Related

- Work item: 104
- Change report: pending
- Other bugs: BUG-API-001 (listing config shadow)
