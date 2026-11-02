---
title: WORK TITLE
id: NN
priority: high | medium | low
status: todo
milestone: 1 | 2 | 3 | 4 | 5 | none
module: A-core | B-hotel | C-folio | D-pos | E-rental | F-services | G-transport | H-weather | I-dashboard | infra | docs | ai-rules
area: backend | frontend | tests | docs | devops
effort: S | M | L
dependencies: none | NN
bug: none | BUG-TYPE-NNN
created: YYYY-MM-DD
updated: YYYY-MM-DD
---

# <Short Title>

## What to do (What)
Short description of the work item. Link docs if useful (`docs/MODULES.md`, scope section).

## Current / Before
Exact current state — file path + exact value/behaviour. Never vague.

## Expected / After
Exact target state after completion.

## Why
Requirement / bug / feature reason. Reference MVP scope or `docs/MILESTONES.md` when applicable.

## Acceptance Criteria (complete only when all ticked)
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Tests run + pass (command) — or N/A with reason
- [ ] `work/changes/YYYY-MM-DD--NN-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `path/to/file`

## Notes / Follow-ups
- Constraints, references, follow-ups.

---

## Return to Pending Log (when work stops midway)

Repeatable block (newest first):

### YYYY-MM-DD
- **Returned by**: <Agent / Name>
- **Reason**: blocked / delayed / user said wait
- **Next step**: ...

---

## Completion Log (when work is complete)

- **Completed by**: <Agent / Name>
- **Date**: YYYY-MM-DD
- **What was done**: short summary
- **Files changed**: ...
- **Verification**: tests/commands run + result (or N/A + why)
- **Change report**: `work/changes/YYYY-MM-DD--NN-<desc>.md`
- **PLAN / SUMMARY**: updated yes/no
