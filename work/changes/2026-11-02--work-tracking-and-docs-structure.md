# Change Report: Work Tracking + Docs Structure

- **Date**: 2026-11-02
- **By**: OpenCode agent
- **Change ID**: setup-001

## 1. Summary
Added the same docs / work-tracking (Kanban) / bug-tracking structure used in `InventorySystem-docker` to this repo.

## 2. Before
- `docs/` did not exist.
- `work/` did not exist.
- `.opencode/skills/` did not exist.
- Repo contained only `README.md` + `document/Mountain_MVP_Scope_v1.pdf`.

## 3. After
- `docs/README.md`, `docs/features/README.md`, `docs/detailed/README.md` — project documentation index and placeholders.
- `work/README.md`, `work/TEMPLATE.md`, `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`.
- `work/todo/`, `work/inprogress/`, `work/completed/` — Kanban folders with README rules.
- `work/bugs/INDEX.md`, `work/bugs/BUG_REPORT_TEMPLATE.md`, `work/bugs/evidence/README.md` — bug lifecycle + backlog.
- `work/changes/CHANGE_REPORT_TEMPLATE.md` — change-report format.
- `work/ci-reports/README.md`, `work/ci-reports/CI_FAILURE_REPORT_TEMPLATE.md` — CI failure reporting.
- `.opencode/skills/work-tracker/SKILL.md`, `.opencode/skills/bug-tracker/SKILL.md`, `.opencode/skills/change-report/SKILL.md` — agent skills that enforce the flow.

## 4. Reason
The project needs one consistent place for planned work, bugs, changes, and documentation before application development starts, matching the workflow already proven in `InventorySystem-docker`.

## 5. Files Affected
- `docs/README.md`
- `docs/features/README.md`
- `docs/detailed/README.md`
- `work/README.md`
- `work/TEMPLATE.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`
- `work/todo/README.md`
- `work/inprogress/README.md`
- `work/completed/README.md`
- `work/bugs/INDEX.md`
- `work/bugs/BUG_REPORT_TEMPLATE.md`
- `work/bugs/evidence/README.md`
- `work/changes/CHANGE_REPORT_TEMPLATE.md`
- `work/changes/2026-11-02--work-tracking-and-docs-structure.md`
- `work/ci-reports/README.md`
- `work/ci-reports/CI_FAILURE_REPORT_TEMPLATE.md`
- `.opencode/skills/work-tracker/SKILL.md`
- `.opencode/skills/bug-tracker/SKILL.md`
- `.opencode/skills/change-report/SKILL.md`

## 6. Verification / Status
- [x] Structure created and cross-checked against `InventorySystem-docker` (`docs/`, `work/`, tracker skills).
- [x] All tables start empty and consistent (`PLAN.md`, `SUMMARY.md`, `bugs/INDEX.md`).
- [ ] Application code not scaffolded yet — no tests to run.
- Notes: Adapt the docs index and MEMORY as the app takes shape.
