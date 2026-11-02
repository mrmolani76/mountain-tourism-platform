# Work Tracking (Kanban Folders)

Every piece of work has one **MD file**. Work moves between folders — exactly like this:

| Folder | Meaning | File Status |
|--------|---------|-------------|
| `work/todo/` | Planned / pending — on the list, not picked up yet | `status: todo` |
| `work/inprogress/` | Work **currently in progress** (picked up) | `status: inprogress` |
| `work/completed/` | Work **finished** | `status: completed` |
| `work/bugs/` | Full bug lifecycle — reports + `INDEX.md` (Open/Verified/Closed) + template | `BUG-<TYPE>-<NNN>` |
| `work/changes/` | Change reports (one MD per change) + template | — |
| `work/ci-reports/` | CI failure reports + template | — |

## Work Flow (NON-NEGOTIABLE)

1. **New work arrives** → create `work/todo/NN-<short-title>.md` (follow `work/TEMPLATE.md`) + add its row to `work/PLAN.md`'s **`## Todo`** table.
2. **BEFORE starting work** → move that MD file:

   ```
   work/todo/NN-title.md  →  work/inprogress/NN-title.md
   ```

   Update the MD `status` frontmatter from `todo` → `inprogress`. Then start coding.
3. **Only 1 work item** can be in-progress at a time (WIP limit = 1), unless the user says otherwise.
4. **Work stopped midway / blocked / returned to pending** → move the MD back:

   ```
   work/inprogress/NN-title.md  →  work/todo/NN-title.md
   ```

   Log the reason in the MD's `## Return to Pending Log` (why it stopped, next step) + `work/PLAN.md`: move its row back to the `## Todo` table (Status = `todo`). To pick it up again, repeat step 2.
5. **As soon as work is complete** → fill in the completion section + verification in the MD, then move:

   ```
   work/inprogress/NN-title.md  →  work/completed/NN-title.md
   ```

6. Every completed item MUST also get a **`work/changes/YYYY-MM-DD--<desc>.md`** change report (see `work/changes/CHANGE_REPORT_TEMPLATE.md`).
7. Update **`work/PLAN.md`** on every move — it has **three separate tables**: `## Todo` (only `todo` rows), `## In Progress` (only `inprogress` rows), `## Completed` (only `completed` rows). Rows are **moved between the tables** so the table always matches the item's folder — never a single mixed table:
   - New / returned-to-pending items → row in **Todo**
   - Picked-up items → row **moves to In Progress**
   - Completed items → row **moves to Completed**
8. **THEN ask the user: "Should I commit and push?"** No `git commit` / `git push` until the user explicitly approves — working tree stays dirty otherwise.
9. On every status change, refresh **`work/SUMMARY.md`** so the dashboard matches `work/PLAN.md`.

## Files

- `work/PLAN.md` — master plan with three separate tables (Todo / In Progress / Completed)
- `work/SUMMARY.md` — master dashboard (Work/Bugs/Testing/Changes/Deploys) — refresh on every status change
- `work/MEMORY.md` — session handoff notes
- `work/TEMPLATE.md` — work-item MD format
- `work/todo/` — pending work
- `work/inprogress/` — work in progress
- `work/completed/` — completed work
- `work/bugs/` — bug reports + `INDEX.md` backlog + `BUG_REPORT_TEMPLATE.md`
- `work/changes/` — change reports + `CHANGE_REPORT_TEMPLATE.md`
- `work/ci-reports/` — CI failure reports + `CI_FAILURE_REPORT_TEMPLATE.md`

## Work item file naming

```
work/todo/01-product-module-tests.md
work/inprogress/01-product-module-tests.md
work/completed/01-product-module-tests.md
```

Ascending numbering, hyphen-separated lowercase title.
