---
name: change-report
description: MUST activate after any change made in this workspace (config/env/db-name, seeder, folder rename, virtual host, docker, docs, or any code). Every work completion must produce a change-report MD file under work/changes/ that records what was done, what it was before, what it is now, and why.
---

# Change Report — Mandatory MD After Every Change

**Trigger**: Whenever any change is completed in this workspace — before reporting "done"/"finished"/"complete". This includes, but is not limited to:
- config / env / DB name changes
- seeder / migration / user changes
- folder renames / path updates
- web server / nginx config changes
- docker / docker-compose changes
- any code, frontend, backend, or documentation edit

**Rule (NON-NEGOTIABLE)**: No change is considered complete until its change-report file exists under `work/changes/`.

**Related mandatory reports** (same "no done without report" rule):
- **Deployment**: after every deploy, add an entry to the deploy log / `work/SUMMARY.md` §5 Deploys. Smoke-check after deploy is mandatory.
- **Bug**: when any confirmed bug is found, create `work/bugs/BUG-<TYPE>-<NNN>-<short>.md` **before** the fix (template `work/bugs/BUG_REPORT_TEMPLATE.md`). Reference the bug ID in the change report and work MD.

## Required Steps

### 1. Create / Update the Report File
- Path: `work/changes/YYYY-MM-DD--<short-description>.md`
- Example: `2026-11-02--work-tracking-structure.md`
- For a new change → create a new dated file.
- For follow-up to same work → append a dated update to the existing file (never delete historical facts).

### 2. Follow the Template
Read and follow `work/changes/CHANGE_REPORT_TEMPLATE.md` exactly. Every report MUST include:

1. **Summary** — what changed, in one line.
2. **Before** — exact file path + exact old value/code/name/db-name.
3. **After** — exact file path + exact new value/code/name/db-name.
4. **Reason** — requirement / bug / feature / setup reason.
5. **Files Affected** — every file created / modified / deleted.
6. **Verification / Status** — tested? migrations/scripts pending? notes.

### 3. Before & After Must Be Concrete
Use **actual values** (exact paths, exact names, exact db names). Never write vague text like "updated config" — write what the old value was and what the new value is.

### 4. Refresh Master Dashboard (`SUMMARY.md`)
After creating a change report, add an entry to `work/SUMMARY.md` §4 Changes (date + report + one-line summary). Order: report first → SUMMARY → done.

## Refusal Conditions
- **Do NOT report done/finished/complete** until the report file is written.
- If a change is partial/blocked, still write the report and record exactly what remains and the next action.

## Output Format (Required)
When declaring a change complete, reference the report file:
```
Change complete. Report: work/changes/YYYY-MM-DD--<desc>.md
(Summary: ... | Before: ... | After: ... | Why: ...)
```
