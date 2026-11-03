---
id: BUG-APP-001
title: Prod SPA blank screen — Nuxt modules not registered (defineStore is not defined)
status: VERIFIED
type: APP
severity: high
created: 2026-11-03
---

# BUG-APP-001: Prod SPA blank screen — Nuxt modules not registered

## 1. Summary
Production SPA (static build served by nginx in `mtmtp_spa`) renders a blank page. Browser console: `Uncaught ReferenceError: defineStore is not defined`.

## 2. Environment
Local Docker dev stack on this machine. `mtmtp_spa` container (image `mtmtp-spa`, nginx 1.27-alpine), repo `main` @ `45737ee` + uncommitted item-24 changes.

## 3. Steps to Reproduce
1. `docker compose -f docker-compose.dev.yml up -d --build`
2. Open `http://127.0.0.1:3000/` in Chrome
3. Page stays blank; DevTools console shows the error

## 4. Expected
Login page renders (email/password form).

## 5. Actual
Blank page. `#__nuxt` stays empty.

## 6. Evidence
Headless Chrome (`--enable-logging=stderr`), before fix:

```
[CONSOLE] "Uncaught ReferenceError: defineStore is not defined", source: http://127.0.0.1:3000/_nuxt/CfLWM5uW.js (37)
```

Bundle contained verbatim (un-transformed) identifiers: `defineStore`, `usePreferredColorScheme`, `useMediaQuery`, `useWindowScroll`, `useDevice`. Nuxt core auto-imports (`useCookie`) were transformed, module-provided ones were not.

## 7. Root Cause
`frontend/nuxt.config.ts` had **no `modules` array**. `@pinia/nuxt` (defineStore), `@vueuse/nuxt` (useMediaQuery, useWindowScroll, usePreferredColorScheme) and `@nuxtjs/device` (useDevice) were never registered, so their auto-imports were never injected into the build. `@nuxtjs/i18n` (useI18n in theme components) was also missing. Inventory SPA registers all four (`modules: ['@vueuse/nuxt', '@nuxtjs/i18n', '@nuxtjs/device', '@pinia/nuxt']` + `i18n: i18nConfig`).

## 8. Workaround
None (page unusable).

## 9. Suggested Fix
Register the four modules in `nuxt.config.ts` and wire `i18n: i18nConfig as any` (same as InventorySystem-docker SPA).

## 10. Impact
Entire SPA unusable in the production/static build, including the login screen. Dev server unaffected in theory (same config hole; would fail the same way on first module-eval).

## 11. Related
- Work item: 25
- Change report: `work/changes/2026-11-03--25-spa-nuxt-modules-blank-screen.md`
- Other bugs: none
