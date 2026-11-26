---
title: Manual browser acceptance checks, one module at a time
id: 103
priority: high
status: todo
milestone: 5
module: A-core
area: tests
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Manual browser acceptance checks, one module at a time

## What to do (What)

Walk through the running local application in a browser, following the checklist below in order. Record PASS, FAIL, or BLOCKED and a short note for each step. Do not move to the next step until the current step is recorded.

## Current / Before

- The repository tracks browser E2E automation separately in `work/todo/76-browser-e2e-testing.md`.
- No current manual browser walkthrough or per-module result log is tracked.
- At checklist creation, work item 102 was in progress and covered the Staff listing header defect. It was later returned to todo to allow item 103 to start.

## Expected / After

- A manual browser walkthrough is recorded step by step against a disposable local tenant.
- Any confirmed defect has a bug report before a fix is started.
- Tests use the existing local app setup. Do not install packages or start/reset infrastructure as part of this item.

## Why

The project needs a repeatable, visible browser check of the core staff workflows and module acceptance paths before pilot handover.

## Acceptance Criteria (complete only when all ticked)

- [ ] Preflight: confirm SPA loads and the API health endpoint responds.
- [ ] Authentication: verify valid login, invalid login feedback, logout, and role-aware access.
- [ ] Core: check Staff listing headers/filters, Buildings, Outlets, Seasons, and tenant settings. Staff listing check follows completion of item 102.
- [ ] Hotel: check availability, reservations, stays, check-in, and check-out.
- [ ] Folio: check charge, payment, void reason, and zero-balance checkout. Never edit or delete Charge or Payment records.
- [ ] POS: check catalog, order creation, settlement, and room charge where available.
- [ ] Rental: check item rent-out, return, and late/damage handling where available.
- [ ] Services: check catalog, slot capacity, booking, and outing headcount where available.
- [ ] Transport and lifts: check trip, passenger list, lift status, and pass flows where available.
- [ ] Weather: check limits, warnings, and weather cancellation behavior.
- [ ] Reports: compare visible totals with their source records.
- [ ] Record PASS/FAIL/BLOCKED for every step and create bug reports before fixing confirmed defects.
- [ ] `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md` updated with actual results.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected

- `work/todo/103-manual-browser-acceptance-checks.md`
- `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`
- `work/bugs/` if browser checks confirm defects

## Notes / Follow-ups

- Use only a disposable local test tenant and test account. Do not use production guest or payment data.
- Do not add a browser-test dependency in this manual walkthrough. Playwright automation remains item 76.
- Stop on a failed prerequisite and record the blocker instead of masking it or changing data to force a pass.

## Browser Run Log

### 2026-11-26
- **Preflight**: BLOCKED. Browser connections were refused at `http://lab.local.mtmtp.local:3000/`, `http://localhost:3000/`, and `http://127.0.0.1:8010/api/health/`.
- **Action**: Stopped after the failed prerequisite. No Docker or other infrastructure was started.
- **Next step**: Resume after the existing local app stack is available or the user explicitly authorizes starting it.

---

## Return to Pending Log (when work stops midway)

### 2026-11-26
- **Returned by**: Copilot
- **Reason**: Browser preflight endpoints refused connections; this item disallows starting or resetting infrastructure without authorization.
- **Next step**: Wait for the user to confirm the local app stack is ready, then resume item 103.

---

## Completion Log (when the browser walkthrough is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**: `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`
- **PLAN / SUMMARY**:
