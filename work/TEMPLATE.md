---
title: WORK TITLE
id: NN
priority: high | medium | low
status: todo
module: <module>
area: backend | frontend | tests | docs
effort: S | M | L
dependencies: none | NN
created: YYYY-MM-DD
---

# <Short Title>

## What to do (What)
Short description of the work item.

## Current / Before
Exact current state — file path + exact value/behaviour. Never vague.

## Expected / After
Exact target state after completion.

## Why
Requirement / bug / feature reason.

## Acceptance Criteria (complete only when all ticked)
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Tests run + pass (command)

## Files Likely Affected
- `path/to/file`

## Notes / Follow-ups
- Any constraints, references, follow-ups.

---

## Return to Pending Log (when work stops midway)

- **Returned by**: <Agent / Name>
- **Date**: YYYY-MM-DD
- **Reason**: blocked / delayed / user milestone
- **Next step**: ...

---

## Completion Log (when work is complete)

- **Completed by**: <Agent / Name>
- **Date**: YYYY-MM-DD
- **What was done**: short summary
- **Files changed**: ...
- **Verification**: tests/commands run + result
- **Change report**: `work/changes/YYYY-MM-DD--<desc>.md`
