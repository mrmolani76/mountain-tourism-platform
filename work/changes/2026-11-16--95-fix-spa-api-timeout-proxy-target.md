# Change Report: Fix SPA API timeout — proxy target

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 95

## 1. Summary

Nuxt `/api` + `/sanctum` proxy now defaults to `http://127.0.0.1:8000` (TCP) while keeping browser Host for tenant resolve, so missing `*.localhost` / `*.mtmp.local` no longer causes API timeouts.

## 2. Before

- Proxy target / `BACKEND_URL` used `lab.localhost:8000` or `demo.mtmp.local:8000`
- Unresolvable hostname → proxy hang → SPA "API timed out"
- CSRF failure was silent

## 3. After

- Default + `.env.example` + local `.env`: `BACKEND_URL=http://127.0.0.1:8000`
- `changeOrigin: false` unchanged (Host = browser tenant host)
- CSRF timeout shows a clear toast

## 4. Reason

User: API timeout, nothing works; code only (no setup/run).

## 5. Files Affected

- `frontend/nuxt.config.ts`
- `frontend/.env.example`
- `frontend/.env`
- `frontend/composables/core/useApi.ts`
- `frontend/docs/LOCAL_SETUP.md`
- `docs/LOCAL_MTMP_SETUP.md`
- `work/completed/95-…`, `work/PLAN.md`, `work/SUMMARY.md`

## 6. Verification / Status

- [ ] Not run (code-only)
- [ ] Notes: Nuxt must be restarted to pick up new `BACKEND_URL`; Laravel still must listen on `:8000` when human runs stack
