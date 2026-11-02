---
title: Frontend SPA — copy into repo and wire to Django backend
id: 23
priority: high
status: completed
milestone: 1
module: A-core
area: frontend
effort: L
dependencies: 18
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Frontend SPA — copy into repo and wire to Django backend

## What to do
Copy the existing Nuxt + Vuetify template from `C:\Users\Anish\Documents\GitHub\frontend-spa`
into `frontend/` (without `.git`) and rewire it from the Laravel backend to the
Django tenant API.

## Current / Before
- `frontend-spa` is a separate git repo (`laravel-spa`) with a Vuexy Nuxt 4 template.
- `utils/api.ts` calls Laravel Sanctum (`/backend/sanctum/csrf-cookie`, `XSRF-TOKEN`, `X-App-Type`).
- `composables/api-clients/useUserApiClient.ts` calls `/login`, `/user`, `/logout`, `/menus`.
- `stores/authStore.ts` expects `{ status, data: { user, userImage, userPermissions } }`.
- `layouts/components/UserProfile.vue` opens a Laravel Echo socket.
- `nuxt.config.ts` points nitro output at `../laravel-api/public_nuxt/`, dev host
  `lab-local.anish.test` with HTTPS, and branding `MyShop`.
- No `frontend/` folder exists in `mountain-tourism-platform`.

## Expected / After
- `frontend/` contains the template source (no `.git`, no build output).
- `utils/api.ts` targets `/api` with Django session cookies + `X-CSRFToken` from the
  `csrftoken` cookie, and parses the Django error envelope.
- Auth calls map to `/api/auth/login/`, `/api/auth/me/`, `/api/auth/logout/`.
- Static mountain tourism navigation (hotel, folio, POS, rental, services, transport,
  weather, reports) replaces the `/menus` endpoint.
- Login, auth guard, logout, and profile work against Django staff sessions.
- Module placeholder pages exist so every menu item resolves.
- Branding is Mountain Tourism.
- Dev proxy forwards `/api` to the Django tenant host without CORS.
- `.env.example` documents the required values.

## Why
Frontend was deferred until the DRF modules existed (items 18–22). The template is
Laravel-specific and cannot talk to Django staff sessions as-is. CLAUDE.md requires the
Nuxt app under `frontend/`.

## Acceptance Criteria (complete only when all ticked)
- [ ] `frontend/` folder exists with template source, no nested `.git`
- [ ] `utils/api.ts` uses Django CSRF + session flow
- [ ] `useUserApiClient` auth calls point at `/auth/*`
- [ ] `authStore` consumes the Django `/auth/me/` shape
- [ ] Static module menu + placeholder pages
- [ ] Branding updated (title, theme config)
- [ ] `nuxt.config.ts` dev proxy + no Laravel output paths
- [ ] `.env.example` documents backend URL
- [ ] Backend `MeSerializer` exposes `role_code` for UI role checks
- [ ] Backend local settings allow the Nuxt dev origin for CSRF
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `frontend/utils/api.ts`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `frontend/stores/authStore.ts`
- `frontend/types/user.ts`
- `frontend/utils/navigation.ts` (new)
- `frontend/utils/app-modules.ts` (new)
- `frontend/layouts/components/DefaultLayoutWithVerticalNav.vue`
- `frontend/layouts/components/UserProfile.vue`
- `frontend/pages/login.vue`
- `frontend/pages/index.vue`
- `frontend/pages/user-profile.vue`
- `frontend/pages/modules/[module].vue` (new)
- `frontend/themeConfig.ts`
- `frontend/nuxt.config.ts`
- `frontend/.env.example`
- `backend/modules/core/serializers/staff.py`
- `backend/config/settings/local.py`

## Notes / Follow-ups
- Full CRUD screens for each module are follow-up work.
- Production must serve the SPA and API on the same origin (session cookies are
  SameSite=Lax), with `/api` proxied to Django.

## Return to Pending Log
None.

## Completion Log
None.
