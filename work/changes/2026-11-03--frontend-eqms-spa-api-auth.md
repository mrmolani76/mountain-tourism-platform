# Change Report: Frontend EQMS SPA API / auth alignment

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-spa-eqms

## 1. Summary
Mountain Nuxt SPA now follows eQMS `qualitycenter-spa` layout for HTTP + auth (shared `API` choke point, `useAuthService`, host helpers, deep docs), using ofetch instead of axios.

## 2. Before
- HTTP via `utils/api.ts` `$api` + `useApiClient` only.
- Login / me / logout lived inside `useUserApiClient` with a one-off `ensureLaravelCsrfCookie`.
- No host → tenant/saas helper; no `composables/core/useApi` or `composables/auth/*`.
- Docs did not map eQMS LOCAL_SETUP / CLAUDE SPA rules to this repo.

## 3. After
- `composables/core/useApi.ts` — singleton `API` with Sanctum CSRF promise cache + 401 handling.
- `composables/auth/useAuthService.ts` — login / logout / getUserDetails / reset helpers.
- `utils/tenantHost.ts` — `lab.localhost` / `saas.localhost` (+ `.mountain.local`) detection.
- `useApiClient` routes through `API`; `useUserApiClient` re-exports auth from `useAuthService`.
- `authStore` uses auth service; alias `useAuthStore`.
- `pages/login.vue` + `LoginDialog.vue` call `useAuthService` / `useAuthStore`.
- Docs: `frontend/docs/EQMS_SPA_API_AUTH.md`, `frontend/docs/LOCAL_SETUP.md`.

## 4. Reason
User asked to match eQMS qualitycenter-spa (check its MD in depth) for how the SPA talks to Laravel.

## 5. Files Affected
- `frontend/composables/core/useApi.ts` (new)
- `frontend/composables/auth/useAuthService.ts` (new)
- `frontend/utils/tenantHost.ts` (new)
- `frontend/utils/api.ts`
- `frontend/composables/api-clients/useApiClient.ts`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `frontend/stores/authStore.ts`
- `frontend/pages/login.vue`
- `frontend/components/dialogs/LoginDialog.vue`
- `frontend/docs/EQMS_SPA_API_AUTH.md` (new)
- `frontend/docs/LOCAL_SETUP.md` (new)
- `work/changes/2026-11-03--frontend-eqms-spa-api-auth.md` (this file)

## 6. Verification / Status
- [ ] Tested / Verified? No — code-only; smoke on setup machine still pending (item 71).
- [ ] Migrations/Scripts pending? N/A (frontend).
- [ ] Notes: axios not added (no drive-by deps). SaaS admin pages / middleware host branch still deferred.
