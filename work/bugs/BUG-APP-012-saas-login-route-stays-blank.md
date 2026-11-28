---
id: BUG-APP-012
title: SaaS login route stays blank after session probe
status: VERIFIED
type: APP
severity: high
created: 2026-11-27
---

# BUG-APP-012: SaaS login route stays blank after session probe

## 1. Summary
The SaaS login route displayed a blank page for an unauthenticated browser session instead of the login form.

## 2. Environment
Local Windows development, Nuxt dev server at `http://saas.mtmtp.local:3000`.

## 3. Steps to Reproduce
1. Open `http://saas.mtmtp.local:3000/saas/login` with no SaaS session.
2. Wait for the Nuxt client app to load.

## 4. Expected
The platform administrator sign-in form renders. An unauthenticated session probe must not prevent the public login page from rendering.

## 5. Actual
The login form now renders on `http://saas.mtmtp.local:3000/saas/login`. The expected unauthenticated `saas/auth/me` request returns HTTP 401 after the page mounts.

## 6. Evidence
- The active Nuxt route module includes `path: "/saas/login"` and the `saas-login` route.
- The browser requests `pages/saas/login.vue?macro=true` and `/api/saas/auth/me`.
- Before the fix, the auth probe ran before the public-login branch and the page root remained empty.
- After moving the public-login branch ahead of the session probe and probing after mount, the browser shows the Email, Password, and Sign in controls.
- The `saas/auth/me` HTTP 401 is expected for an unauthenticated session and no longer blocks rendering.

## 7. Root Cause
The global middleware awaited the session probe before handling the public login route. The page could remain suspended while the unauthenticated probe completed.

## 8. Workaround
The login route renders first and performs the existing-session check after mount.

## 9. Suggested Fix
Verified in browser after moving the public-login branch before session bootstrap and running the session check after mount.

## 10. Impact
The blank-page issue blocked Super Admin sign-in in a fresh browser session. Tenant creation and tenant-login acceptance remain pending a Super Admin session. No production tenant data or money records are affected.

## 11. Related
- Work item: `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- Change report: `work/changes/2026-11-27--114-saas-owner-retry-and-admin.md`
- Other bugs: `BUG-ENV-004`
