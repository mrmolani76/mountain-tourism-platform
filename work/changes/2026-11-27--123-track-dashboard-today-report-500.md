# Change Report: Track Dashboard Today-Report Failure

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 123-track-dashboard-today-report-500

## 1. Summary
Recorded the reproducible owner-dashboard today-report HTTP 500 as BUG-APP-009, then prioritized its investigation at the user's request.

## 2. Before
The browser showed that dashboard figures could not load, and the Laravel request log showed `/api/reports/today` returning HTTP 500. No bug report or dedicated work item tracked this failure.

## 3. After
Added BUG-APP-009 and work item 123, then moved item 123 to `work/inprogress/` and returned item 103 to `work/todo/` to honor the WIP limit. Root cause remains unconfirmed.

## 4. Reason
The manual browser acceptance run exposed a repeatable dashboard API failure. The issue is tracked without changing unrelated application code.

## 5. Files Affected
- `work/bugs/BUG-APP-009-dashboard-today-report-500.md`
- `work/bugs/INDEX.md`
- `work/inprogress/123-investigate-dashboard-today-report-500.md`
- `work/todo/103-manual-browser-acceptance-checks.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/inprogress/103-manual-browser-acceptance-checks.md`

## 6. Verification / Status
- [x] Browser reproduced the dashboard fallback and Laravel request logs showed repeated HTTP 500 responses from `/api/reports/today`.
- [x] Root cause left explicitly undetermined; no code fix was attempted.
- [x] Work tracking updated; item 123 is the sole in-progress item and item 103 is back in Todo.
- [ ] Focused investigation and fix remain in progress.
