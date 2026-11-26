---
title: Build public weather lobby screen with lift status
id: 113
priority: high
status: todo
milestone: 5
module: H-weather
area: frontend
effort: M
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Build public weather lobby screen with lift status

## What to do (What)
Complete the public lobby display by adding a Nuxt screen and exposing lift status alongside active weather warnings and avalanche information.

## Current / Before
The backend has a token-based public lobby endpoint that returns warnings and today's avalanche level, but omits lift status. No corresponding public lobby screen was found in the Nuxt pages.

## Expected / After
An active lobby token opens a read-only public screen with current warnings, avalanche level, and lift statuses. A lift closure is reflected within one minute.

## Why
Module G requires closed lift status on the lobby screen within one minute. Module H/Milestone 5 includes the lobby screen and avalanche display.

## Acceptance Criteria (complete only when all ticked)
- [ ] Include tenant-scoped lift status and relevant status timestamps in the public lobby payload.
- [ ] Add a public, token-based Nuxt lobby screen without staff login requirements.
- [ ] Refresh or otherwise update status within the one-minute contract.
- [ ] Show required forecast attribution and handle inactive/invalid screen tokens.
- [ ] Add API/UI coverage for lift closure visibility and public access behavior.
- [ ] `work/changes/YYYY-MM-DD--113-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Weather/App/Services/LobbyService.php`
- `backend-laravel/Modules/Weather/routes/api.php`
- `backend-laravel/Modules/Transport/App/Models/Lift.php`
- `frontend/pages/`
- `frontend/composables/api-clients/`
- `backend-laravel/tests/`

## Notes / Follow-ups
- Keep the public endpoint scoped to the current tenant host and opaque screen token.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

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
