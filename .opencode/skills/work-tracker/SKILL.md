---
name: work-tracker
description: >-
  MUST activate before starting ANY task or work item in this repo. Kanban
  folder workflow — pick the work MD from work/todo/, move it to
  work/inprogress/ BEFORE edits, and move it to work/completed/ when done.
  Trigger keywords: work item, task, pending, in-progress, inprogress, complete,
  pick up, start work.
---

# Work Tracker — Kanban Folders

Every piece of work has one **MD file** that moves between folders.

## Folders (`work/`)

| Folder | Meaning | status |
|--------|---------|--------|
| `work/todo/` | Planned | `todo` |
| `work/inprogress/` | Active | `inprogress` |
| `work/completed/` | Finished | `completed` |

Supporting: `work/PLAN.md`, `work/TEMPLATE.md`, `work/README.md`, `work/SUMMARY.md`.

## Rules (NON-NEGOTIABLE)

1. New work → `work/todo/NN-<short-title>.md` from `work/TEMPLATE.md` + row in `PLAN.md` Todo table.
2. BEFORE edits → move to `work/inprogress/`, set `status: inprogress`, move PLAN row to In Progress.
3. WIP limit = 1 unless user says otherwise.
4. Blocked → move back to `todo/` with Return to Pending Log.
5. Complete → fill Completion Log, `status: completed`, move to `work/completed/`, update PLAN.
6. Every completion needs a `change-report` under `work/changes/`.
7. Refresh `work/SUMMARY.md` on status changes.
8. Never delete work-item MDs; archive in `completed/`.
9. After completion ask: **"Commit aur push karun?"** (CLAUDE.md Rule 2). No commit until yes.

## Naming

```
work/todo/01-core-tenant-models.md
```
