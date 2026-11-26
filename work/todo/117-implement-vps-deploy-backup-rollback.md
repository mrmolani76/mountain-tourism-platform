---
title: Implement tenant-aware VPS deploy backup and rollback
id: 117
priority: high
status: todo
milestone: 5
module: infra
area: devops
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Implement tenant-aware VPS deploy backup and rollback

## What to do (What)
Deliver the Laravel-compatible VPS deployment and operational backup/restore/rollback path required by the MVP.

## Current / Before
`docs/VPS_SETUP.md` contains Python/Django/Gunicorn instructions and backs up one database. The repository has quality-check CI only, with no deployment workflow, release retention, tested tenant database restore or rollback evidence.

## Expected / After
A documented and implemented deployment path installs the current Laravel/Nuxt app, migrates central and tenant databases safely, retains the latest five releases, backs up all required databases nightly with seven-day retention, and has tested restore and rollback procedures.

## Why
MVP §6 and §11 require a rebuildable VPS, deployment process, nightly backups, tested restore, and a quick release rollback.

## Acceptance Criteria (complete only when all ticked)
- [ ] Replace obsolete Django VPS instructions with the approved Laravel + Nuxt topology.
- [ ] Add deployment automation with health check and safe rollback on failed release.
- [ ] Retain the latest five releases and document safe migration/rollback constraints.
- [ ] Back up the central and tenant databases nightly and retain the last seven backups.
- [ ] Perform and record one restore test and one release rollback test in a non-production environment.
- [ ] Ensure secrets are supplied only through approved environment/GitHub secret mechanisms.
- [ ] `work/changes/YYYY-MM-DD--117-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `docs/VPS_SETUP.md`
- `docs/HOSTING.md`
- `.github/workflows/`
- `scripts/`

## Notes / Follow-ups
- No deploy, SSH, production database, or destructive operation without explicit user authorization.
- Do not add deployment secrets to the repository.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
