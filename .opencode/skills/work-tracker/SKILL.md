---
name: work-tracker
description: MUST activate before starting ANY task or work item in this repo. The project runs a Kanban folder workflow — pick the work MD from work/todo/, move it to work/inprogress/ BEFORE doing edits, and move it to work/completed/ when done. Trigger keywords: work item, task, pending, in-progress, inprogress, complete, pick up, start work.
---

# Work Tracker — Kanban Folders

Every piece of work has one **MD file** that moves between folders as its status changes.

## Folders (`<repo>/work/`)

| Folder | Meaning | status value |
|--------|---------|--------------|
| `work/todo/` | Planned / pending — on the list, not picked up yet | `todo` |
| `work/inprogress/` | Currently being worked on | `inprogress` |
| `work/completed/` | Finished | `completed` |

Supporting files:
- `work/PLAN.md` — master list with **three separate tables**: `## Todo` (only `todo` rows), `## In Progress` (only `inprogress` rows), `## Completed` (only `completed` rows). Rows are MOVED between tables on every status change so the table always matches the item's folder — never a single mixed table.
- `work/TEMPLATE.md` — work-item MD format
- `work/README.md` — flow explanation
- `work/SUMMARY.md` — master dashboard; refresh on every status change

## Rules (NON-NEGOTIABLE)

1. **New work** → create `work/todo/NN-<short-title>.md` following `work/TEMPLATE.md`. Also add its row to `work/PLAN.md`'s **Todo** table.
2. **BEFORE starting work** (before any edit/command) → move the MD:

   ```
   work/todo/NN-title.md → work/inprogress/NN-title.md
   ```

   Update the MD `status` frontmatter from `todo` → `inprogress`. **Move its row from `work/PLAN.md`'s `## Todo` table to the `## In Progress` table.**
3. **WIP limit = 1**: only one item may be in-progress at a time, unless the user says otherwise. If a file already exists in `inprogress/`, complete it first (or ask the user).
4. **Return to pending** (blocked / delayed / user said stop) → move the MD back `work/inprogress/` → `work/todo/`. Set `status` frontmatter → `todo`, log the reason in `## Return to Pending Log` (why it stopped, next step). **Move its row back from `## In Progress` to `## Todo`** (Status = `todo`). To pick it up again, repeat step 2.
5. **On completion** → fill in the MD's `## Completion Log` section: completed by, date, what was done, files changed, verification (tests/commands + results), change-report path. Set `status` frontmatter → `completed`. Then move:

   ```
   work/inprogress/NN-title.md → work/completed/NN-title.md
   ```

6. Every completed item MUST also get a **`work/changes/YYYY-MM-DD--<desc>.md`** change report (see `change-report` skill / `work/changes/CHANGE_REPORT_TEMPLATE.md`).
7. On every status change, update `work/PLAN.md` by **moving the row between `## Todo` / `## In Progress` / `## Completed`** so the table matches the item's folder. Never leave a row in the wrong table.
8. Use `mv` (or `git mv` if in the repo) for moves. NEVER delete a work-item MD — the file archives in `work/completed/`.
9. **After completion + change report, MUST ask the user: "Should I commit and push?"** Do NOT `git commit` / `git push` / `gh pr create` until the user explicitly approves. Working tree stays dirty until then.
10. On every status change, **refresh `work/SUMMARY.md` §1 Work** so the dashboard matches `work/PLAN.md`.

## Work item file naming

```
work/todo/01-product-module-tests.md
work/inprogress/01-product-module-tests.md
work/completed/01-product-module-tests.md
```

Ascending numbering, hyphen-separated lowercase title.

## Trigger

Use this skill when:
- The user assigns any new task/work
- The user asks to pick up an item from the work/todo list
- A task is about to be completed — verify completion + move + change report

If an MD file is in the wrong folder while working, move it to the correct folder (because **path = status**).
