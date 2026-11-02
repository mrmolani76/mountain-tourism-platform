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
2. Include Summary, Before, After, Reason, Files Affected, Verification
3. Use concrete paths and values (never vague "updated config")
4. Refresh `work/SUMMARY.md` Changes section
5. Reference the report when declaring done

## Refusal

Do not say done/finished/complete without the report file.
