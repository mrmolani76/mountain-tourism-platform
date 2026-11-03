# Change Report: Fix BUG-APP-001 — prod SPA blank screen

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 25 (bug: BUG-APP-001)

## 1. Summary
Production SPA rendered a blank page with `Uncaught ReferenceError: defineStore is not defined`. Root cause: `frontend/nuxt.config.ts` had no `modules` array, so `@pinia/nuxt`, `@vueuse/nuxt`, `@nuxtjs/device` and `@nuxtjs/i18n` were never registered and their auto-imports were never injected into the static bundle. Registered all four (same wiring as the inventory SPA) and wired `i18n: i18nConfig as any`.

## 2. Before
- `nuxt.config.ts`: no `modules` key, no `i18n` key, `i18n.config.ts` unimported
- Bundle contained verbatim `defineStore(`, `usePreferredColorScheme(`, `useMediaQuery(`, `useDevice(` → module-eval crash → `#__nuxt` empty
- Reproduced with headless Chrome: console `Uncaught ReferenceError: defineStore is not defined` at `/_nuxt/CfLWM5uW.js`

## 3. After
- `modules: ['@vueuse/nuxt', '@nuxtjs/i18n', '@nuxtjs/device', '@pinia/nuxt']`
- `import i18nConfig from './i18n.config'` + `i18n: i18nConfig as any` (mirrors InventorySystem-docker SPA)
- `mtmtp_spa` rebuilt; headless Chrome `--virtual-time-budget=15000 --dump-dom http://127.0.0.1:3000/` → `#__nuxt` populated (auth-wrapper + auth-card, email/password form); console log empty; container healthy

## 4. Reason
User report (2026-11-03): "browser hit to hogya hai but kuch show nhi horha hai" → browser console showed the ReferenceError. Confirmed bug BUG-APP-001 (high).

## 5. Files Affected
- `frontend/nuxt.config.ts`
- `work/bugs/BUG-APP-001-spa-blank-screen-nuxt-modules.md` (new)
- `work/bugs/INDEX.md`, `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`
- `work/inprogress/25-fix-bug-app-001-spa-blank-screen.md`

## 6. Verification / Status
- [x] `docker compose -f docker-compose.dev.yml up -d --build spa` — build OK, container healthy
- [x] Headless Chrome DOM dump: login page mounts, zero console errors
- [x] SPA `/` → 200 via nginx; `/api/health/` proxy still 200
- Notes: actual staff login requires a tenant host (`LoginView` rejects central hosts); tenant DB provisioning is out of scope here.

## 7. Usage
```bash
docker compose -f docker-compose.dev.yml up -d --build spa
./status.sh
```
