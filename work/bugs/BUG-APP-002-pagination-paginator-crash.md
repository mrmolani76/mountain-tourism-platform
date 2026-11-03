---
id: BUG-APP-002
title: All list endpoints 500 — pagination uses nonexistent self.paginator
status: VERIFIED
type: APP
severity: high
created: 2026-11-03
---

# BUG-APP-002: All list endpoints 500 — pagination uses `self.paginator`

## 1. Summary
Every DRF list endpoint crashed with `AttributeError: 'StandardResultsSetPagination' object has no attribute 'paginator'`.

## 2. Environment
Local Docker dev stack; `backend/common/pagination/__init__.py` (item 18).

## 3. Steps to Reproduce
1. `GET /api/buildings/` (or any list endpoint) with an authenticated session
2. Response 500

## 4. Expected
Envelope `{ success, data: [...], meta: {...} }` with page metadata.

## 5. Actual
500 Internal Server Error.

## 6. Evidence
```
AttributeError: 'StandardResultsSetPagination' object has no attribute 'paginator'
  File "backend/common/pagination/__init__.py", line 25, in get_paginated_response
```

## 7. Root Cause
`get_paginated_response` read `self.paginator.num_pages` / `.count`, but DRF's `PageNumberPagination` exposes only `self.page` (a Django `Page`); `paginator` lives at `self.page.paginator`.

## 8. Workaround
None.

## 9. Suggested Fix
Use `self.page.number`, `self.page.paginator.num_pages`, `.count`, `self.page.has_next()/has_previous()`.

## 10. Impact
Every list API in the tenant core (buildings, outlets, seasons, staff, …) unusable.

## 11. Related
- Work item: 28
- Change report: `work/changes/2026-11-03--28-module1-test-run-fixes.md`
