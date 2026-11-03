---
name: work-tracker
description: >-
  MUST activate before starting ANY task or work item in this repo. Kanban
  folder workflow — pick the work MD from work/todo/, move it to
  work/inprogress/ BEFORE edits, and move it to work/completed/ when done.
  Also activate to create a new work item, return to pending, or sync
  PLAN.md/SUMMARY.md. Trigger keywords: work item, task, pending, in-progress,
  inprogress, complete, pick up, start work, next todo, kanban.
---

# Work Tracker — Kanban Folders

**Path = status.** One MD per work item. Full human doc: `work/README.md`.  
Rules: `CLAUDE.md` Rule 10 + § Work Tracking.

## Folders

| Folder | `status` |
|--------|----------|
| `work/todo/` | `todo` |
| `work/inprogress/` | `inprogress` |
| `work/completed/` | `completed` |

Also: `work/PLAN.md` (3 tables), `work/SUMMARY.md`, `work/TEMPLATE.md`, `work/MEMORY.md`.

---

## A. Create new work

1. Next ID = max `NN` in todo+inprogress+completed + 1 (pad: `01`, `02`, …).
2. Copy `work/TEMPLATE.md` → `work/todo/NN-<short-title>.md`.
3. Fill frontmatter + What / Before / After / Why / Acceptance Criteria.
4. Add row under `work/PLAN.md` → `## Todo` only.
5. Refresh `work/SUMMARY.md` §1 Todo.

Milestone tip: title like `01-m1-tenant-scoped-manager` (see `docs/MILESTONES.md`).

---

## B. Pick up (MANDATORY before edits)

**Stop if** `work/inprogress/` already has a file and user did not allow WIP > 1.

```bash
mv work/todo/NN-title.md work/inprogress/NN-title.md
```

1. Frontmatter `status: inprogress`
2. PLAN: move row Todo → In Progress
3. SUMMARY §1 refresh
4. Optional: one line in `work/MEMORY.md` under Current focus

Only then edit application/docs files.

---

## C. Return to pending

```bash
mv work/inprogress/NN-title.md work/todo/NN-title.md
```

1. `status: todo`
2. Fill `## Return to Pending Log` (who, date, reason, next step)
3. PLAN: In Progress → Todo
4. SUMMARY refresh
5. Update MEMORY.md

---

## D. Complete

1. Fill `## Completion Log` completely.
2. `status: completed`
3. `mv work/inprogress/NN-title.md work/completed/NN-title.md`
4. PLAN: In Progress → Completed (+ date)
5. **Required:** `change-report` → `work/changes/YYYY-MM-DD--NN-<desc>.md`
6. SUMMARY §1 + §4
7. MEMORY.md: move item to session completed notes
8. Notify: `bash scripts/todo-notify.sh NN` — emails completion to anhali736@gmail.com (no-op if `scripts/.notify.env` is missing; never fails the flow)
9. Ask user: **"Commit aur push karun?"** — no git commit/push until yes

---

## E. Sync / repair

If folder, frontmatter, and PLAN disagree:

1. Trust **folder location** as source of truth for status.
2. Fix frontmatter `status` to match folder.
3. Move PLAN row into the matching table (only one table).
4. Refresh SUMMARY.
5. Never delete a work MD to “clean up” — archive in `completed/` or return to `todo/`.

Validate anytime:

```bash
bash scripts/check-work-kanban.sh
```

---

## Refusal conditions

- Do **not** start coding on a tracked task while its MD is still in `todo/`.
- Do **not** leave two items in `inprogress/` unless user approved.
- Do **not** mark complete without Completion Log + change report.
- Do **not** delete work-item MDs.
- Do **not** commit/push without explicit user approval after complete.
