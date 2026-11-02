---
name: bug-tracker
description: MUST activate whenever a confirmed bug is found (runtime/test/deploy/smoke/user-reported) and on every bug-status change. Creates work/bugs/BUG-<TYPE>-<NNN>-<short>.md from the template, adds/keeps a row in work/bugs/INDEX.md, and drives the BUG_CREATED → … → COMPLETED status flow with traceability. Trigger keywords: bug, confirmed bug, 500, regression, fail, broken.
---

# Bug Tracker — System Skill

**Purpose**: Every confirmed bug has ONE report file in `work/bugs/` whose `Status` frontmatter is always current, and every bug appears in the master backlog `work/bugs/INDEX.md` in the table matching its status.

## Files

| File | Role |
|------|------|
| `work/bugs/BUG-<TYPE>-<NNN>-<short>.md` | Bug report (template: `work/bugs/BUG_REPORT_TEMPLATE.md`) |
| `work/bugs/INDEX.md` | Master backlog — 3 tables: `## Open` / `## Verified` / `## Closed` |
| `work/SUMMARY.md` | Master dashboard — §2 Bugs mirrors all tables; refresh on every transition |
| `work/todo/NN-*.md` | Fix work item (via `work-tracker`) that references the Bug ID |
| `work/changes/*.md` | Change report on fix/verify |
| `work/bugs/evidence/` | Sanitized logs, snapshots, screenshots |

## Status Flow (NON-NEGOTIABLE)

```
BUG_CREATED → BUG → TODO → IN_PROGRESS → BUG_FIX_IN_PROGRESS → RETESTING → VERIFIED → COMPLETED
```

Mapping to INDEX tables (a row is ONLY ever in one table, matching the file's Status):

| Status | INDEX table | Notes |
|--------|-------------|-------|
| `BUG_CREATED`, `BUG`, `TODO`, `IN_PROGRESS`, `BUG_FIX_IN_PROGRESS`, `RETESTING` | `## Open` | Actively open — fix not verified yet |
| `VERIFIED` | `## Verified` | Fix in, tests pass, awaiting completion + change report |
| `COMPLETED` | `## Closed` | Final — change report written, archive state |

## Workflow

### 1. New bug → create report BEFORE any fix
1. **Duplicate check first** — scan `work/bugs/*.md` for the same bug. Do not duplicate.
2. Pick next `NNN` per TYPE (`APP|ENV|API|DATA|AUTH|TEST|FLAKY`) — maximum existing per type + 1.
3. Create `work/bugs/BUG-<TYPE>-<NNN>-<short-description>.md` following `work/bugs/BUG_REPORT_TEMPLATE.md` **exactly** (Summary / Environment / Steps to Reproduce / Expected / Actual / Evidence / Root Cause / Workaround / Suggested Fix / Impact / Related).
4. `Status: BUG_CREATED`. Evidence-based Root Cause mandatory — no guessing.
5. Add row to `work/bugs/INDEX.md` → `## Open` table.
6. Create the fix work item (`work/todo/NN-*.md` via `work-tracker` TEMPLATE) and reference the Bug ID in it. In the bug report `## 11. Related`, reference the work item and change report.
7. On every bug transition, refresh `work/SUMMARY.md` §2 Bugs — keep INDEX and SUMMARY in sync.

### 2. Fix in progress
- Pick up the fix item via `work-tracker` (`work/todo/` → `work/inprogress/`); set bug `Status` → `IN_PROGRESS` (or `BUG_FIX_IN_PROGRESS`) — row stays in Open; only update Status.

### 3. Fix done + retested
- Bug file `Status → VERIFIED`; move INDEX row `## Open → ## Verified`; refresh `SUMMARY.md` §2 Bugs (Open→Verified). Add verification evidence (tests run + pass) to the bug file.

### 4. Completed
- After change report and explicit **"Should I commit and push?"** approval: bug `Status → COMPLETED`; move INDEX row `## Verified → ## Closed`; refresh `SUMMARY.md` §2 (Verified→Closed). Never commit/push without explicit user approval.

### 5. Return to open (fix rejected / retest failed)
- On retest fail from `VERIFIED` or any open state → `Status → RETESTING` (or `IN_PROGRESS`), row back in Open (move from Verified if applicable). Triage again before fixing.

## Refusal Conditions
- **Never fix a confirmed bug without a report** — report first, fix after.
- Never create a duplicate bug report — search first.
- Never mark `VERIFIED`/`COMPLETED` without evidence (tests + root cause + retest/regression).
- Never put secrets/tokens/passwords in bug reports, INDEX, or logs — redact.
- Never edit the INDEX to a row status mismatch — file Status == table must always agree.

## Traceability
`BUG-<TYPE>-<NNN>` → fix work item (`work/todo/NN`) → change report (`work/changes/...`) → (on deploy) deploy entry. Reference the bug ID everywhere.

## Interaction With Other Skills / Rules

| Situation | Use |
|-----------|-----|
| Failing/flaky test classification before any bug | triage first |
| Fix work item pickup/completion | `work-tracker` |
| Claiming done/verified | verify first |
| After any change | `change-report` |
| Commit/push | ask the user first |
