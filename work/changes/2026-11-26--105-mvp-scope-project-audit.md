# Change Report: MVP Scope and Project Audit

- **Date**: 2026-11-26
- **By**: Copilot
- **Change ID**: 105

## 1. Summary
Added an evidence-based audit of MVP scope Modules A-I, implementation relationships, acceptance gaps and release handover readiness.

## 2. Before
- No current scope-to-code completion report existed.
- The project had stale or conflicting delivery docs and no single relationship map for the implemented modules.
- Generic folio void requests could omit `reason_code`, with the service defaulting to `weather`.

## 3. After
- Added [MVP_SCOPE_AUDIT.md](../../docs/MVP_SCOPE_AUDIT.md), with module status, relationship map, concrete gaps, evidence limits and prioritized next work.
- Recorded the optional-reason-code issue as [BUG-MONEY-001](../bugs/BUG-MONEY-001-default-void-reason-weather.md) and created pending fix item 106.
- Updated the docs index and kanban/dashboard/handoff state for item 105.

## 4. Reason
The user requested a complete comparison of the project against the full MVP scope PDF, including module progress and data relationships.

## 5. Files Affected
- `docs/MVP_SCOPE_AUDIT.md`
- `docs/README.md`
- `work/bugs/BUG-MONEY-001-default-void-reason-weather.md`
- `work/bugs/INDEX.md`
- `work/todo/103-manual-browser-acceptance-checks.md`
- `work/todo/106-fix-generic-folio-void-reason.md`
- `work/completed/105-mvp-scope-project-audit.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`
- `work/changes/2026-11-26--105-mvp-scope-project-audit.md`

## 6. Verification / Status
- [x] Read all nine pages of the scope PDF and cross-checked code, migrations, routes, UI, tests and delivery docs.
- [x] Confirmed findings against source files, including command scheduling, tenant timezone usage, outlet attribution, lobby payload and void reason defaults.
- [x] No application code or tenant records changed.
- [ ] Automated tests/builds not run. This was a documentation-only audit, and the current CI workflow explicitly does not run tests.
- [ ] Full browser acceptance remains pending; this report records the partial Hotel browser results available from the earlier session.
- [x] Kanban status reconciled manually; automated tracker script was not rerun because its known CRLF issue was already recorded.
- [x] Completion notification script was run with CRLF normalized in memory; it skipped email because `scripts/.notify.env` is absent.
