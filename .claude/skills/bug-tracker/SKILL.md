---
name: bug-tracker
description: >-
  MUST activate whenever a confirmed bug is found and on every bug-status
  change. Creates work/bugs/BUG-<TYPE>-<NNN>-<short>.md, updates
  work/bugs/INDEX.md, and keeps work/SUMMARY.md in sync. Triggers: bug,
  confirmed bug, 500, regression, fail, broken.
---

# Bug Tracker

## Files

| File | Role |
|------|------|
| `work/bugs/BUG-<TYPE>-<NNN>-<short>.md` | Bug report |
| `work/bugs/INDEX.md` | Open / Verified / Closed tables |
| `work/SUMMARY.md` | Dashboard § Bugs |
| `work/todo/NN-*.md` | Fix work item referencing Bug ID |

TYPE examples: `APP`, `ENV`, `API`, `DATA`, `AUTH`, `TEST`, `FLAKY`, `MONEY`, `TENANT`.

## Flow

```
BUG_CREATED → TODO → IN_PROGRESS → RETESTING → VERIFIED → COMPLETED
```

1. Duplicate check in `work/bugs/`
2. Create report from `BUG_REPORT_TEMPLATE.md` **before** fixing
3. Add INDEX Open row + SUMMARY refresh
4. Create/fix work item via `work-tracker`
5. On verify: move to Verified with test evidence
6. On complete: change report + ask commit/push + Closed

## Refusal

Never fix a confirmed bug without a report first.
