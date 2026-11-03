---
title: Fix BUG-APP-001 — prod SPA blank screen (register Nuxt modules)
id: 25
priority: high
status: completed
milestone: 1
module: infra
area: frontend
effort: S
dependencies: 24
bug: BUG-APP-001
created: 2026-11-03
updated: 2026-11-03
---

# Fix BUG-APP-001 — prod SPA blank screen

## What to do (What)
Register missing Nuxt modules so the production/static SPA boots and the login page renders.

## Current / Before
- `frontend/nuxt.config.ts` had no `modules` array; `@pinia/nuxt`, `@vueuse/nuxt`, `@nuxtjs/device`, `@nuxtjs/i18n` never registered
- Static bundle contained un-transformed `defineStore(...)` etc.; module eval threw `Uncaught ReferenceError: defineStore is not defined`; `#__nuxt` stayed empty (blank page)
- Reproduced with headless Chrome against `mtmtp_spa` at `http://127.0.0.1:3000/`

## Expected / After
- `modules: ['@vueuse/nuxt', '@nuxtjs/i18n', '@nuxtjs/device', '@pinia/nuxt']` + `i18n: i18nConfig as any` wired (same as inventory SPA)
- Login page renders; zero console errors

## Why
User: "browser hit to hogya hai but kuch show nhi horha hai" (2026-11-03). Root-caused to BUG-APP-001.

## Acceptance Criteria (complete only when all ticked)
- [x] `nuxt.config.ts` registers the 4 modules + i18n config
- [x] SPA rebuilt; headless Chrome renders login form; no console errors
- [x] `work/bugs/BUG-APP-001-spa-blank-screen-nuxt-modules.md` created (VERIFIED)
- [x] `work/changes/2026-11-03--25-spa-nuxt-modules-blank-screen.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `frontend/nuxt.config.ts`

## Notes / Follow-ups
- Inventory SPA uses the identical modules list; keep in sync if more theme modules are added
- Actual staff login still requires a tenant host (see LoginView); tenant DB provisioning is a separate setup decision

---

## Return to Pending Log (when work stops midway)

None.

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Registered `@vueuse/nuxt`, `@nuxtjs/i18n`, `@nuxtjs/device`, `@pinia/nuxt` in `frontend/nuxt.config.ts` and wired `i18n: i18nConfig as any`. Rebuilt `mtmtp_spa`; headless Chrome renders the login page (email/password form) with zero console errors.
- **Files changed**: `frontend/nuxt.config.ts`
- **Verification**: headless Chrome `--virtual-time-budget=15000 --dump-dom http://127.0.0.1:3000/` → `#__nuxt` populated (auth-wrapper, auth-card, login form); console error log empty; SPA container healthy
- **Change report**: `work/changes/2026-11-03--25-spa-nuxt-modules-blank-screen.md`
- **PLAN / SUMMARY**: updated yes
