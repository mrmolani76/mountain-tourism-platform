# Change Report — Work Item 23: Frontend SPA — copy into repo and wire to Django backend

## What changed

### Frontend (`frontend/`)

- **Copy of `frontend-spa`** into `mountain-tourism-platform/frontend/` (713 files), excluding `.git`, `.nuxt`, `.output`, `node_modules`.
- **`frontend/utils/api.ts`** — rewritten for Django: `$api` with base URL `${apiBaseUrl}/api`, `credentials: 'include'`, automatic `X-CSRFToken` from `csrftoken` cookie on unsafe methods (POST/PUT/PATCH/DELETE), 401/403 error handling, unauthenticate + redirect to `/login`.
- **`frontend/types/user.ts`** — added `StaffUser`, `StaffSessionPayload` interfaces; `User` now has optional `roleCode`, `tenantSlug`, and compact role fields from Django `/auth/me/`.
- **`frontend/composables/api-clients/useUserApiClient.ts`** — auth methods mapped to Django endpoints:
  - `login` → POST `/auth/login/`, returns `{ status: true, data: mappedUser }`.
  - `logout` → POST `/auth/logout/`.
  - `getUserDetails` → GET `/auth/me/`, returns `{ status, data: { user, staff, userImage, userPermissions } }`.
  - `getMenuItems` → returns static `appNavigation` (mountain tourism modules).
  - `mapStaffToUser` — maps Django `StaffUser` → template `User` with all fields.
  - Role-based permissions map: `ROLE_PERMISSIONS`.
- **`frontend/stores/authStore.ts`** — updated `saveUser` to handle new response shape; `logoutUser` preserves flow; added return value.
- **`frontend/middleware/auth.global.ts`** — new route middleware: probes session on auth-required pages, redirects to `/login` when unauthenticated, respects `public` / `unauthenticatedOnly` meta.
- **`frontend/utils/app-modules.ts`** — static module list (hotel, folio, pos, rental, services, transport, weather, reports) with `getAppModule` and `appNavigation` vertical nav items (Dashboard + Operations + modules + Account/Profile). Links use route objects `{ path: '...' }` to avoid `@layouts/utils` `resolveRoutes` path-dropping.
- **`frontend/layouts/components/DefaultLayoutWithVerticalNav.vue`** — uses static `appNavigation` instead of API fetch; removed `resolveRoutes` call.
- **`frontend/layouts/components/UserProfile.vue`** — removed Echo/socket usage (`socketStore().getUserChannel`); profile page is now read-only, displays staff details from Django session.
- **`frontend/pages/login.vue`** — simplified: removed `resetPasswordOTP` branch and forgot-password link; handles `login` returning null; shows Mountain Tourism branding.
- **`frontend/pages/index.vue`** — rewired as dashboard: welcome, role/tenant display, health check via `/api/health/`, module cards (8 modules), append-only money note.
- **`frontend/pages/user-profile.vue`** — read-only account page: shows `email`, `roleName`, `roleCode`, `tenantSlug`, `staff ID` from Django; no profile/password forms; sign-out button.
- **`frontend/pages/modules/hotel.vue`** through **`frontend/pages/modules/reports.vue`** — placeholder pages using `ModulePlaceholder` component, each showing module title, description, endpoint, features, and a "Next step" alert.
- **`frontend/components/modules/ModulePlaceholder.vue`** — reusable component that displays a module's info and API schema.
- **`frontend/themeConfig.ts`** — title changed from `MyShop` to `Mountain Tourism`; logo switched to `VIcon tabler-mountain`.
- **`frontend/nuxt.config.ts`** — removed `ssr: false` (kept), removed `title: 'MyShop'`, changed to `Mountain Tourism`, removed favicon href, changed `devServer` host from `lab-local.anish.test` to `localhost`, port 3000, HTTPS off; removed `nitro.output.publicDir` Laravel path; added `vite.server.proxy /api` → `BACKEND_URL || http://lab.local.mountain.local:8000` with `changeOrigin: true, secure: false`.
- **`frontend/.env`** and **`frontend/.env.example`** — documents `NUXT_PUBLIC_API_BASE_URL`, `ENV`, `BACKEND_URL`.

### Backend (`backend/`)

- **`backend/modules/core/serializers/staff.py`** — `MeSerializer` now returns `role` (display label via `get_role_display`), `role_code` (stable code from `role` field), and `tenant_slug`. `StaffSerializer` keeps `id`, `email`, `role`, `is_active`, `created_at`.
- **`backend/config/settings/local.py`** — added `CSRF_TRUSTED_ORIGINS` from `DJANGO_CSRF_TRUSTED_ORIGINS` env var, defaulting to `http://localhost:3000,http://127.0.0.1:3000`. This allows the Nuxt dev proxy (origin `localhost:3000`) to reach Django's CSRF check while the proxy forwards the tenant host.

## Why

The existing Nuxt + Vuetify template was built for a Laravel Sanctum stack (`/sanctum/csrf-cookie`, `/backend/sanctum/*`, `/menus` API). It cannot talk to Django staff sessions as-is. The Django backend (items 18–22) added `MeSerializer` with `role_code` and `CSRF_TRUSTED_ORIGINS` for the Nuxt dev origin. This wiring makes the frontend work against the Django tenant API with session cookies and static navigation.

## Acceptance Criteria (as of this change)

- [x] `frontend/` folder exists with template source, no nested `.git`
- [x] `utils/api.ts` uses Django CSRF + session flow
- [x] `useUserApiClient` auth calls point at `/auth/*`
- [x] `authStore` consumes the Django `/auth/me/` shape
- [x] Static module menu + placeholder pages
- [x] Branding updated (title, theme config)
- [x] `nuxt.config.ts` dev proxy + no Laravel output paths
- [x] `.env.example` documents backend URL
- [x] Backend `MeSerializer` exposes `role_code` for UI role checks
- [x] Backend local settings allow the Nuxt dev origin for CSRF
- [x] Change report written
- [x] PLAN.md + SUMMARY.md updated

## Files changed

| Category | Files |
|---|---|
| Frontend (rewire) | `frontend/utils/api.ts`, `frontend/types/user.ts`, `frontend/composables/api-clients/useUserApiClient.ts`, `frontend/stores/authStore.ts`, `frontend/middleware/auth.global.ts`, `frontend/utils/app-modules.ts`, `frontend/layouts/components/DefaultLayoutWithVerticalNav.vue`, `frontend/layouts/components/UserProfile.vue`, `frontend/pages/login.vue`, `frontend/pages/index.vue`, `frontend/pages/user-profile.vue`, `frontend/pages/modules/hotel.vue`, `frontend/pages/modules/folio.vue`, `frontend/pages/modules/pos.vue`, `frontend/pages/modules/rental.vue`, `frontend/pages/modules/services.vue`, `frontend/pages/modules/transport.vue`, `frontend/pages/modules/weather.vue`, `frontend/pages/modules/reports.vue`, `frontend/components/modules/ModulePlaceholder.vue`, `frontend/themeConfig.ts`, `frontend/nuxt.config.ts`, `frontend/.env`, `frontend/.env.example` |
| Backend (support) | `backend/modules/core/serializers/staff.py`, `backend/config/settings/local.py` |
| Work tracking | `work/inprogress/23-frontend-spa-django-wiring.md` → `work/completed/`, `work/PLAN.md`, `work/SUMMARY.md`, `work/changes/23-frontend-spa-django-wiring.md` |

## Completion Log

- Copied `frontend-spa` → `frontend/` (713 files, no `.git`/build folders).
- Rewired auth: `login`/`logout`/`getUserDetails` → Django `/auth/*` endpoints.
- Mapped Django `StaffUser` → template `User` with `role_code`, `tenantSlug`.
- Replaced dynamic menu fetch with static `appNavigation` (route objects, no `resolveRoutes`).
- Removed Echo/socket from `UserProfile.vue`; profile is read-only.
- Dashboard (`index.vue`) shows health check, role/tenant, module cards.
- Module placeholders (`pages/modules/*`) ready for UI implementation.
- Branding: `Mountain Tourism` throughout.
- Dev proxy: `/api` → Django tenant host, no CORS dependency.
- `.env` + `.env.example` with required values.
- Backend: `MeSerializer.role_code` + `CSRF_TRUSTED_ORIGINS`.