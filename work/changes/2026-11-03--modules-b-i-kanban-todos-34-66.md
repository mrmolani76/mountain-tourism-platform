# Change Report: Module B–I kanban todos 34–66

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: todos-34-66

## 1. Summary
Added executable Module 2–9 (plus shared auth) kanban items under `work/todo/` only. Index + PLAN/SUMMARY/MEMORY synced.

## 2. Before
- Active todos: Module 1 closeout **29–33** only
- Modules B–I had deep specs but no small work-tracker items

## 3. After
- **34**: shared domain ViewSet auth fix
- **35–39**: Module 2 Hotel
- **40–44**: Module 3 Folio
- **45–48**: Module 4 POS
- **49–51**: Module 5 Rental
- **52–55**: Module 6 Services
- **56–59**: Module 7 Transport/lifts
- **60–63**: Module 8 Weather
- **64–66**: Module 9 Reports / MVP exit
- Index: `work/todo/README-all-modules-todos.md`
- PLAN Todo table lists 29–66

## 4. Reason
User asked for todo lists for all remaining modules (not under `work/phases/`).

## 5. Files Affected
- `work/todo/34-*.md` … `work/todo/66-*.md`
- `work/todo/README-all-modules-todos.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`
- `work/changes/2026-11-03--modules-b-i-kanban-todos-34-66.md`

## 6. Verification / Status
- [x] `bash scripts/check-work-kanban.sh`
- [ ] Notes: no application code; planning only
