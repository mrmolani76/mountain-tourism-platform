# Change Report: Module A architecture note + core data model draft

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: work item 02

## 1. Summary
Filled `docs/ARCHITECTURE.md` with Module A foundation decisions and recorded tenant isolation + auth style in `docs/DECISIONS.md`.

## 2. Before
- `docs/ARCHITECTURE.md` had placeholders; isolation/auth undecided; open questions listed.
- `docs/DECISIONS.md` had 8 rows (no isolation mechanism or auth style rows).

## 3. After
- `docs/ARCHITECTURE.md` filled: tenancy (scoped manager), money, auth (session-based), backend layout (`config/` + `apps.core`), entities table, app packages, deploy shape (systemd, no Docker), open questions closed.
- `docs/DECISIONS.md` gained 2 rows: isolation = scoped manager; auth = session-based email/password.

## 4. Reason
PDF Milestone 1 requires architecture note + data model before scaffold; Module A done-when needs tenant isolation approach defined.

## 5. Files Affected
- `docs/ARCHITECTURE.md`
- `docs/DECISIONS.md`
- `work/completed/02-m1-architecture-data-model.md` (status moved todo → completed)
- `work/PLAN.md`, `work/SUMMARY.md` (sync)

## 6. Verification / Status
- [x] Kanban check passed (`scripts/check-work-kanban.sh`)
- [ ] Tests: N/A (docs-only item)
- [ ] Migrations/Scripts pending: N/A
- [x] PLAN.md + SUMMARY.md updated
