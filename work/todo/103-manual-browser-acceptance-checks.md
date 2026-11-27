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
bug: BUG-APP-008
created: 2026-11-26
updated: 2026-11-27
---

# Manual browser acceptance checks, one module at a time

## What to do (What)

Walk through the running local application in a browser, following the checklist below in order. Record PASS, FAIL, or BLOCKED and a short note for each step. Do not move to the next step until the current step is recorded.

## Current / Before

- The repository tracks browser E2E automation separately in `work/todo/121-browser-e2e-testing.md`.
- No current manual browser walkthrough or per-module result log is tracked.
- At checklist creation, work item 104 was in progress and covered the Staff listing header defect. It was later returned to todo to allow item 103 to start.

## Expected / After

- A manual browser walkthrough is recorded step by step against a disposable local tenant.
- Any confirmed defect has a bug report before a fix is started.
- Use the existing local app setup. Do not reset tenant data or install packages as part of this item.

## Why

The project needs a repeatable, visible browser check of the core staff workflows and module acceptance paths before pilot handover.

## Acceptance Criteria (complete only when all ticked)

- [x] Preflight: confirm SPA loads and the API health endpoint responds.
- [ ] Authentication: verify valid login, invalid login feedback, logout, and role-aware access.
- [ ] Core: check Staff listing headers/filters, Buildings, Outlets, Seasons, and tenant settings. Staff listing check follows completion of item 104.
- [ ] Confirm Staff detail/edit routes and API lookups use persisted UUIDs, not numeric IDs. See BUG-APP-008.
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

- `work/inprogress/103-manual-browser-acceptance-checks.md`
- `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`
- `work/bugs/` if browser checks confirm defects

## Notes / Follow-ups

- Use only a disposable local test tenant and test account. Do not use production guest or payment data.
- Do not add a browser-test dependency in this manual walkthrough. Playwright automation remains item 121.
- Stop on a failed prerequisite and record the blocker instead of masking it or changing data to force a pass.

## Browser Run Log

### 2026-11-27
- **Preflight**: PASS. Started `mtmtp_db`, `mtmtp_laravel_local`, and `mtmtp_spa` without migrations or tenant reset. API health, SPA login, and proxied API health returned HTTP 200; browser rendered the Mountain Tourism login page.
- **Migration check**: Central DB has 7 applied migrations; `lab_local` tenant DB has 60 applied migrations (batch 8). These were already present; no migration command was executed.
- **Startup note**: Compose's default Laravel command includes migrations and `tenants:provision-local --reset`, so it was overridden. `docker compose start spa` tried to start the old Laravel container and hit a port conflict; starting the existing SPA container directly succeeded.
- **Next step**: Resume remaining authentication and module checks after dashboard bug item 123.
- **UI request**: Removed the redundant back-arrow button from the Staff detail header at the user's request; retained the edit control.
- **Change report**: `work/changes/2026-11-27--103-remove-staff-detail-back-button.md`.
- **Staff UUID routes**: Updated SPA routes and API handling to use persisted UUIDs, matching Nexro. BUG-APP-008 is verified after the local tenant migration and browser checks.
- **Change report**: `work/changes/2026-11-27--103-staff-uuid-routes.md`.
- **Browser refresh after route update**: PASS. Nuxt HMR serves `http://localhost:3000/login`; after hydration the browser renders the login form. The anonymous session lookup returned HTTP 401 before successful test-account login. No application records were changed during browser checks.
- **Staff UUID migration**: PASS. After user approval, ran only `2026_11_27_000003_add_uuid_to_core_staff.php` against `lab_local`; all 8 Staff rows have non-null, distinct UUIDs. No reset or other migration was run.
- **Staff UUID browser flow**: PASS. Local test login succeeded; Staff listing linked to UUID URLs, and the UUID detail and edit routes loaded the selected Staff record. The edit form was not submitted.
- **Owner dashboard today report**: FAIL. Dashboard showed the today-summary failure message, and Laravel request logs showed repeated HTTP 500 responses from `/api/reports/today`. Tracked as BUG-APP-009 and Todo item 123; no application code was changed.
- **Work priority**: Returned this item to Todo so the dashboard failure can be investigated first under the WIP limit.

### 2026-11-26
- **Preflight**: BLOCKED. Browser connections were refused at `http://lab.local.mtmtp.local:3000/`, `http://localhost:3000/`, and `http://127.0.0.1:8010/api/health/`.
- **Action**: Stopped after the failed prerequisite. No Docker or other infrastructure was started.
- **Next step**: Resume after the existing local app stack is available or the user explicitly authorizes starting it.
- **Retry after user started Docker**: BLOCKED. `http://lab.local.mtmtp.local:3000/` and `http://localhost:3000/` returned `ERR_FAILED`; `http://127.0.0.1:8010/api/health/` and `http://127.0.0.1:8000/api/health/` refused connections.
- **Action**: Did not run Docker commands. Returned the item to todo because the SPA and API are not reachable from the browser.
- **Startup after explicit user authorization**: Started existing Postgres container; launched Laravel with `php artisan serve` directly (did not run the compose `--reset` provisioning command); attached its container to the Compose network with alias `laravel`; started SPA.
- **Preflight retry**: PASS. `http://127.0.0.1:8010/api/health/` returned HTTP 200 with database `ok`; `http://lab.local.mtmtp.local:3000/login` returned HTTP 200 and rendered the Mountain Tourism login page; proxied `http://lab.local.mtmtp.local:3000/api/health/` returned HTTP 200.
- **Next step**: Continue one-by-one browser workflow after the Staff listing header fix.

- **Menu route check**: Started after item 104. The sidebar exposed 55 unique routes. The first navigation pass captured page titles/headings on 37 routes; 18 did not expose an `h1`/`h2` within the wait window, so they remain to be checked by page content. No browser page errors were captured during that pass.
- **Read-only confirmation**: `/rental` overview rendered its title and links for the desk and catalog pages. `/buildings` rendered its title and configured table headers after waiting for async loading. Continue checking all menu routes without submitting forms or changing records.
- **Hotel route pass**: `/hotel`, `/hotel/room-types`, `/hotel/rooms`, `/hotel/hut-dormitories`, `/hotel/hut-beds`, `/hotel/season-prices`, `/hotel/board-plans`, `/hotel/reservations`, `/hotel/stays`, and `/hotel/availability` returned HTTP 200 and rendered their expected page headings. No forms were submitted.

---

## Return to Pending Log (when work stops midway)

### 2026-11-27
- **Returned by**: Copilot
- **Reason**: User asked to prioritize fixing the owner dashboard today-report failure; WIP limit is one.
- **Next step**: Resume the remaining auth and module browser checks after item 123.

### 2026-11-26
- **Returned by**: Copilot
- **Reason**: User reprioritized work to a full MVP PDF versus project implementation audit; WIP limit is one.
- **Next step**: Resume the remaining menu and module acceptance checks after the project audit.

### 2026-11-26
- **Returned by**: Copilot
- **Reason**: User asked to resolve the Staff listing header defect before continuing broader acceptance checks.
- **Next step**: Resume item 103 after item 104 is completed.

---

## Completion Log (when the browser walkthrough is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**: `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`
- **PLAN / SUMMARY**:
