# Change Report: Module 1 closeout todos + move phase 01-todos into work/todo

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: m1-todos-29-33

## 1. Summary
Created Module 1 closeout kanban items 29–33. Removed `work/phases/01-todos/` and moved its design files into `work/todo/`. Kanban checker skips non-`NN-*.md` files.

## 2. Before
- `work/phases/01-todos/` held Module A design checklist
- `work/todo/` empty of active Module 1 closeout items
- Phase README pointed at `01-todos/`

## 3. After
- Kanban todo: `29` tenancy freeze, `30` audit foundation, `31` edge authz tests, `32` policy defaults, `33` Module 1 exit
- Design files: `work/todo/A-core-and-super-admin.md`, `work/todo/README-module-a-todos-design.md`
- `work/phases/01-todos/` removed
- Live pointers updated in `01-foundation.md`, `work/phases/README.md`, `PLAN.md`, `SUMMARY.md`, `MEMORY.md`, `docs/CI.md`
- `scripts/check-work-kanban.sh` ignores non-numeric kanban filenames in status folders

## 4. Reason
User: no todo list under phase; move files into `work/todo/`; small Module 1 work items for flow-wise closeout.

## 5. Files Affected
- `work/todo/29-m1-tenancy-decision-freeze.md`
- `work/todo/30-m1-audit-foundation.md`
- `work/todo/31-m1-edge-authz-tests.md`
- `work/todo/32-m1-policy-defaults.md`
- `work/todo/33-m1-exit-mark-complete.md`
- `work/todo/A-core-and-super-admin.md` (moved)
- `work/todo/README-module-a-todos-design.md` (moved/renamed)
- `work/phases/01-todos/` (removed)
- `work/phases/01-foundation.md`, `work/phases/README.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`
- `docs/CI.md`
- `scripts/check-work-kanban.sh`

## 6. Verification / Status
- [x] `bash scripts/check-work-kanban.sh` (run after write)
- [ ] Migrations/Scripts pending? N/A
- [ ] Notes: pickup starts at **29** (WIP = 1)
