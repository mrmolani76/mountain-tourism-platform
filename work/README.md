# Work Tracking (Kanban Folders)

Every piece of work has one **MD file**. **Folder = status.** Keep `PLAN.md` and `SUMMARY.md` in sync on every move.

| Folder | Meaning | Frontmatter `status` |
|--------|---------|----------------------|
| `work/todo/` | Planned / pending | `todo` |
| `work/inprogress/` | Currently picked up | `inprogress` |
| `work/completed/` | Finished | `completed` |
| `work/bugs/` | Bug lifecycle + `INDEX.md` | `BUG-<TYPE>-<NNN>` |
| `work/changes/` | One MD per change | — |

## Flow (NON-NEGOTIABLE)

### 1. New work
1. Pick next ID = max existing `NN` across `todo/` + `inprogress/` + `completed/` + 1 (zero-pad 2+ digits: `01`, `02`, …).
2. Create `work/todo/NN-<short-title>.md` from `work/TEMPLATE.md`.
3. Add a row to `work/PLAN.md` → **`## Todo`** only.
4. Refresh `work/SUMMARY.md` §1 Work → Todo.

### 2. Pick up (BEFORE any code/doc edits)
```bash
mv work/todo/NN-title.md work/inprogress/NN-title.md
# or: git mv ...
```
1. Set frontmatter `status: inprogress`.
2. Move PLAN row from **`## Todo`** → **`## In Progress`**.
3. Refresh SUMMARY §1.
4. **WIP limit = 1.** If another file is already in `inprogress/`, finish it or ask the user first.

### 3. Return to pending (blocked / delayed / user said wait)
```bash
mv work/inprogress/NN-title.md work/todo/NN-title.md
```
1. Set `status: todo`.
2. Fill `## Return to Pending Log`.
3. Move PLAN row **In Progress** → **Todo**.
4. Refresh SUMMARY.

### 4. Complete
1. Fill `## Completion Log` (what, files, verification, change-report path).
2. Set `status: completed`.
3. Move MD → `work/completed/`.
4. Move PLAN row **In Progress** → **Completed** (add Completed date).
5. Write `work/changes/YYYY-MM-DD--NN-<desc>.md` (`change-report` skill). Prefer including the work ID in the filename.
6. Refresh SUMMARY §1 and §4.
7. Ask: **"Commit aur push karun?"** Do not commit until the user says yes.

## PLAN.md rules
- Three separate tables: Todo / In Progress / Completed.
- A row lives in **exactly one** table, matching the file folder.
- Never keep one mixed table of all statuses.

## SUMMARY.md rules
Master dashboard. On every work/bug/change move, refresh the matching section so it mirrors folders + PLAN + bug INDEX + changes list.

## Related skills
- `work-tracker` — this flow
- `change-report` — mandatory on complete
- `bug-tracker` — bugs first, then a work item for the fix
- `verify-before-done` — before saying done

## Files
| Path | Role |
|------|------|
| `PLAN.md` | Three-table master list |
| `SUMMARY.md` | Dashboard |
| `MEMORY.md` | Session handoff notes |
| `TEMPLATE.md` | Work item format |
| `bugs/` | Bug reports + INDEX |
| `changes/` | Change reports |

## Agent checklist (copy into chat when picking up)

```text
Work pickup:
- [ ] Only one inprogress (or user allowed more)
- [ ] MD moved todo → inprogress
- [ ] status: inprogress
- [ ] PLAN row moved to In Progress
- [ ] SUMMARY refreshed
Then edit code/docs.
```

```text
Work complete:
- [ ] Completion Log filled
- [ ] MD moved inprogress → completed
- [ ] status: completed
- [ ] PLAN row moved to Completed
- [ ] change report written
- [ ] SUMMARY refreshed
- [ ] Asked commit/push
```
