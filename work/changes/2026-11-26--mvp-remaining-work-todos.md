# Change Report: MVP Remaining Work TODOs

- **Date**: 2026-11-26
- **By**: Copilot
- **Change ID**: 107-119

## 1. Summary
Created prioritized, actionable TODO work items for the remaining MVP scope and handover gaps found in the audit.

## 2. Before
- The audit listed remaining gaps, but several had no dedicated work item with acceptance criteria.
- Existing TODO items 103 and 106 covered browser acceptance and the generic folio void reason only.

## 3. After
- Added work items 107-119 under `work/todo/`, covering contract decisions, folio integrity, scheduled jobs, service age, transport departure/maps, public lobby, SaaS UI, module tests, OpenAPI/docs, VPS delivery, demo seed, and handover.
- Added each new item to `work/PLAN.md` and `work/SUMMARY.md`.
- No application code was changed and no commit was made.

## 4. Reason
The user asked for a proper TODO list of the remaining work, with TODO Markdown files only before implementation.

## 5. Files Affected
- `work/todo/107-confirm-mvp-contract-decisions.md`
- `work/todo/108-protect-folio-ledger-integrity.md`
- `work/todo/109-schedule-tenant-local-background-work.md`
- `work/todo/110-add-service-minimum-age.md`
- `work/todo/111-reservation-departure-dropoff-flow.md`
- `work/todo/112-use-osm-for-location-and-route-maps.md`
- `work/todo/113-build-public-weather-lobby-screen.md`
- `work/todo/114-add-saas-super-admin-tenant-ui.md`
- `work/todo/115-add-module-d-to-i-acceptance-tests.md`
- `work/todo/116-document-openapi-and-align-project-docs.md`
- `work/todo/117-implement-vps-deploy-backup-rollback.md`
- `work/todo/118-build-complete-demo-tenant-seed.md`
- `work/todo/119-complete-mvp-handover-walkthrough.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-26--mvp-remaining-work-todos.md`

## 6. Verification / Status
- [x] Confirmed there was no active in-progress item before creating the TODOs.
- [x] Confirmed IDs 107-119 follow the highest existing work item ID 106.
- [ ] Application tests/builds not run; no application code was changed.
- [ ] Commit not created; user asked to prepare TODOs first.
