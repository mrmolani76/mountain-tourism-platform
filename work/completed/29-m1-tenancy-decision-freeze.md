---
title: Module 1 — freeze tenancy decision (DB-per-tenant)
id: 29
priority: high
status: completed
milestone: 1
module: A-core
area: docs
effort: S
dependencies: 28
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 1 — freeze tenancy decision (DB-per-tenant)

## What to do (What)
Close Module_1 deep-spec §19 Q1–Q2 against existing `docs/DECISIONS.md`. Document that MVP isolation = DB-per-tenant + Host/`Domain` resolve (not shared-table `tenant_id` filter). Link PDF “tenant from login / no purchased subdomain” to current host routing.

## Current / Before
- `docs/DECISIONS.md` already has Accepted rows for DB-per-tenant + connection isolation (2026-11-02).
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` §3.2 / §19 still lists Q1–Q2 as open for client/owner.
- Agents may hesitate to treat Module 1 as closable.

## Expected / After
- Short decision note (DECISIONS and/or Module_1 cross-link) states Q1–Q2 resolved for MVP.
- No architecture rewrite in this item.

## Why
Module 1 exit needs a settled isolation story. PDF wording and repo differ; owner already chose DB-per-tenant earlier — freeze it explicitly for Module 1 closeout.

## Acceptance Criteria (complete only when all ticked)
- [x] Q1–Q2 marked resolved with pointer to DECISIONS
- [x] Host/`Domain` vs “no subdomain purchase” clarified in one place
- [x] No app architecture rewrite
- [x] `work/changes/YYYY-MM-DD--29-<desc>.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `docs/DECISIONS.md`
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` (optional status note)
- `docs/ARCHITECTURE.md` (only if a one-line sync needed)

## Notes / Follow-ups
- Code-only default: docs only.
- Next: 30 audit foundation.

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Froze MVP tenancy story: DECISIONS.md +2026-11-03 Module 1 freeze row; Module_1 §3.2 RESOLVED note; §19 Q1–Q2 marked resolved (pointers to DECISIONS). Docs-only; no app code.
- **Files changed**: `docs/DECISIONS.md`, `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`
- **Verification**: DECISIONS row present (append-only); spec points to it; no code touched
- **Change report**: `work/changes/2026-11-03--29-m1-tenancy-decision-freeze.md`
- **PLAN / SUMMARY**: updated yes
