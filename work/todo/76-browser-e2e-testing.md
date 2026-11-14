---
title: Browser / E2E testing for frontend (Nuxt + Vuetify)
id: 76
priority: high
status: todo
milestone: 2
module: A-core
area: frontend
effort: L
dependencies: 71
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Browser / E2E testing for frontend

## What to do (What)

Add Playwright E2E test suite for `frontend/` (Nuxt 4 + Vuetify 3, SSR off, :3000). Cover auth login/logout, dashboard health check, hotel availability, and folio checkout zero-balance gate.

## Current / Before

- `frontend/` has zero test files (no `.spec.*`, no `.test.*`, no `e2e/` dir).
- No test runner installed: package.json has no vitest, playwright, cypress, or @vue/test-utils.
- Pages to cover: `/login`, `/`, `/hotel/availability`, `/hotel/stays`, `/hotel/reservations`.

## Expected / After

- `frontend/tests/e2e/` with Playwright config + spec files for auth, dashboard, hotel availability, checkout gate.
- `playwright.config.ts` with baseURL `http://localhost:3000`, Chrome/webkit/firefox projects.
- Test credentials sourced from env (never hardcoded real passwords).
- `work/changes/2026-11-13--76-browser-e2e-testing.md` written.
- PLAN.md + SUMMARY.md updated.

## Why

Browser testing is the next gate after backend Laravel work (items 69–75). Critical user journeys (login → dashboard → availability → checkout) must be verified end-to-end before Module 3 folio UI.

## Acceptance Criteria (complete only when all ticked)

- [ ] `frontend/tests/e2e/playwright.config.ts` authored
- [ ] `frontend/tests/e2e/auth.spec.ts` — login valid/invalid, logout
- [ ] `frontend/tests/e2e/dashboard.spec.ts` — health check, user/tenant cards
- [ ] `frontend/tests/e2e/hotel-availability.spec.ts` — date range + building filter + DataTable
- [ ] `frontend/tests/e2e/folio-checkout-gate.spec.ts` — checkout blocked on non-zero balance
- [ ] Prerequisite noted: `npm install -D @playwright/test` + `npx playwright install` must be run before executing
- [ ] Tests NOT run (no runner installed yet) — ready to run once package added
- [ ] `work/changes/...` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `frontend/tests/e2e/**`
- `frontend/package.json` (devDependency note only — do not install)
- `work/todo/76-browser-e2e-testing.md`
- `work/PLAN.md`, `work/SUMMARY.md`

## Notes / Follow-ups

- Do NOT install `@playwright/test` or run browsers unless user explicitly overrides (AGENTS.md setup rule + coding-agent code-only mode).
- Use `page.goto('/login')` with `baseURL` from config; do not hardcode `http://localhost:3000` inside specs.
- Auth: POST `/sanctum/csrf-cookie` then `/api/auth/login` proxied via Nuxt dev server.
- Money rule: checkout gate asserts folio balance is zero before allowing checkout (void-only correction).

---

## Return to Pending Log (when work stops midway)

### 2026-11-13
- **Returned by**: agent
- **Reason**: authored test files + plan; runner not installed, tests not executed
- **Next step**: user to `npm install -D @playwright/test` + `npx playwright install`, then run `npx playwright test`

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
