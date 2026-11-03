---
title: Module 1 — MVP policy defaults for open questions
id: 32
priority: medium
status: completed
milestone: 1
module: A-core
area: docs
effort: S
dependencies: 29
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 1 — MVP policy defaults for open questions

## What to do (What)
Document MVP defaults for Module_1 §19 questions that still block day-to-day coding (deactivation behaviour, Owner outlet access, staff fields, etc.). Mark true client-only questions as deferred.

## Current / Before
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` §19 lists ~19 open questions
- Season overlap already enforced in code (item 28 / BUG-APP-003)
- Agents may treat every question as a blocker

## Expected / After
- Short defaults note in `docs/DECISIONS.md` (or Module_1 appendix) for coding defaults
- Remaining client asks clearly deferred
- No product feature rebuild in this item

## Why
Close Module 1 without waiting on every edge policy; keep coding unblocked.

## Acceptance Criteria (complete only when all ticked)
- [ ] Defaults written for questions that affect current APIs
- [ ] Deferred questions listed without blocking Module 1 exit
- [ ] `work/changes/YYYY-MM-DD--32-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `docs/DECISIONS.md`
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` (optional status notes)

## Notes / Follow-ups
- Docs only. No setup.

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Documented MVP coding defaults for Module_1 §19 Q3–Q18 (docs-only): complex create (name+slug mandatory), active-on-create, deactivated tenant → 404, deactivation temporary/reactivable, staff fields, manager password reset / owner staff create, Owner all-outlets implicit + staff outlet access, building/outlet multiple types & fixed enums, seasons no-overlap, tax_rates % list, new-tenant defaults (EUR/metric/UTC), audit Owner/Manager view + indefinite retention. Marked Q19 resolved (isolation). True client-only questions deferred.
- **Files changed**: `docs/DECISIONS.md`, `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`
- **Verification**: DECISIONS row present (append-only); spec §19 Q19 marked RESOLVED; no app code
- **Change report**: `work/changes/2026-11-03--32-m1-policy-defaults.md`
- **PLAN / SUMMARY**: updated yes
