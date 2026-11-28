---
id: BUG-ENV-004
title: Nuxt dev server retains stale SaaS route imports
status: VERIFIED
type: ENV
severity: medium
created: 2026-11-27
---

# BUG-ENV-004: Nuxt dev server retains stale SaaS route imports

## 1. Summary
The running Nuxt development app can retain stale generated routes. It now tries to import the removed SaaS edit page path `[id]/edit.vue` although the source route is `[uuid]/edit.vue`.

## 2. Environment
Local Windows development, Nuxt dev server at `http://saas.mtmtp.local:3000`.

## 3. Steps to Reproduce
1. Open `http://saas.mtmtp.local:3000/saas/tenants`.
2. Observe the Vite import-analysis overlay.

## 4. Expected
Nuxt's generated route table imports the current SaaS page files, including `[uuid]/edit.vue`, and the tenant list renders.

## 5. Actual
Before restart, the browser displayed `[plugin:vite:import-analysis] Failed to resolve import "/app/pages/saas/tenants/[id]/edit.vue?macro=true"` from the generated `virtual:nuxt:/app/.nuxt/routes.mjs`. Reloading did not clear it. After restarting Nuxt, the login form renders and unauthenticated tenant-list access redirects there.

## 6. Evidence
- The current source page exists at `frontend/pages/saas/tenants/[uuid]/edit.vue`; there is no `[id]` directory in the source tree.
- Before restart, the browser's generated route module imported the old `[id]/edit.vue` path and returned a Vite import-analysis error.
- After restart, the route module resolves `[uuid]/edit.vue`, and browser navigation renders the SaaS login form.

## 7. Root Cause
The running Nuxt dev server had a stale generated route module from before the dynamic tenant route was renamed from `[id]` to `[uuid]`. The source route was present under `[uuid]`, but the runtime route manifest still referenced `[id]`.

## 8. Workaround
Restarting the Nuxt development server regenerated the route module. The browser then rendered the login form.

## 9. Suggested Fix
Restarted the local `mtmtp_spa_dev` container. The route module now imports `[uuid]/edit.vue`, and `/saas/tenants` redirects unauthenticated users to the visible SaaS login page.

## 10. Impact
A stale route table temporarily blocked the local SaaS tenant list and edit routes. No production tenant data or money records are affected.

## 11. Related
- Work item: `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- Change report: `work/changes/2026-11-28--114-stale-nuxt-route-import.md`
- Other bugs: `BUG-APP-012`
