---
name: change-report
description: >-
  MUST activate after any change in this workspace (config, env, db, docker,
  docs, or code). Every completion needs a change-report MD under work/changes/
  with before/after/why. Trigger: done, finished, complete, changed, updated.
---

# Change Report — Mandatory MD After Every Change

**Rule**: No change is complete until `work/changes/YYYY-MM-DD--<desc>.md` exists. (CLAUDE.md Rule 9)

## Steps

1. Create report from `work/changes/CHANGE_REPORT_TEMPLATE.md`
2. Prefer filename `YYYY-MM-DD--NN-<desc>.md` when tied to a work item ID
3. Include Summary, Before, After, Reason, Files Affected, Verification
4. Use concrete paths and values (never vague "updated config")
5. Link the report path inside the work item `## Completion Log`
6. Refresh `work/SUMMARY.md` §4 Changes
7. Reference the report when declaring done

## With work-tracker

Complete order: fill Completion Log → move MD to `completed/` → write this report → update PLAN/SUMMARY → ask commit/push.

## Refusal

Do not say done/finished/complete without the report file.
