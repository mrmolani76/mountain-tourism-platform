---
id: BUG-APP-003
title: Season overlap rule rejected same-kind only; cross-kind overlaps accepted
status: VERIFIED
type: APP
severity: medium
created: 2026-11-03
---

# BUG-APP-003: Season overlap rule same-kind only

## 1. Summary
`validate_season_overlap` documented "applies across kinds" but filtered `Season.objects.filter(kind=kind)`, so a winter and a summer could overlap the same dates; API returned 201 instead of 400.

## 2. Environment
Local Docker dev stack; `backend/modules/core/services/config.py`.

## 3. Steps to Reproduce
1. Create winter season 2026-12-01..2027-04-15
2. POST summer season 2027-04-01..2027-10-31 → expected 400 (dates overlap winter), got 201
3. Test: `tests/api/test_core_config.py::CoreConfigTests::test_season_overlap_rejected`

## 4. Expected
400 with `starts_on` validation error (dates overlap an existing season).

## 5. Actual
201 Created; overlapping seasons stored.

## 6. Evidence
```
E       AssertionError: 201 != 400
tests/api/test_core_config.py:238
```

## 7. Root Cause
Implementation filtered by `kind` although the documented MVP rule is cross-kind; only same-kind overlaps were rejected.

## 8. Workaround
None.

## 9. Suggested Fix
Drop the `kind` filter (`Season.objects.all()`), keep `exclude_id` behaviour.

## 10. Impact
Overlapping winter/summer/closed seasons break season resolution, pricing and menus per season.

## 11. Related
- Work item: 28
- Change report: `work/changes/2026-11-03--28-module1-test-run-fixes.md`
