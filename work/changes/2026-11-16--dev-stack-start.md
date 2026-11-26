# Change Report: mtmtp dev stack start + URL unblock

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: dev-stack-start

## 1. Summary

`http://lab.local.mtmtp.local:3000/` refused to connect because the mtmtp Docker
stack was not running (only the separate nexro stack was up). Started the dev
stack and verified login end to end.

## 2. Before

- `docker compose -f docker-compose.dev.yml ps` returned no containers.
- `http://lab.local.mtmtp.local:3000/login` -> connection refused.
- Ports 3000 / 8010 / 5433 had no listener.

## 3. After

- `docker compose -f docker-compose.dev.yml up -d` started `mtmtp_db`,
  `mtmtp_laravel`, `mtmtp_spa`.
- Container bootstrap ran composer install, key:generate, migrate,
  `tenants:provision-local lab`, then `php artisan serve`.
- `GET /api/health/` -> 200 (direct 8010 and via SPA 3000).
- `GET http://lab.local.mtmtp.local:3000/login` -> 200.
- `POST /api/auth/login` (`owner@lab.test` / `password123`) -> 200,
  `tenant_slug: lab`.

## 4. Reason

User: "url hi nhi chalrha hai". Root cause was the stopped stack, not config.

## 5. Files Affected

- Runtime only (Docker). No source changes.
- `frontend/playwright.config.ts` baseURL edit was reverted; playwright work
  is deferred at user request.

## 6. Notes

- `http://lab.local.mtmtp.local/backend` (port 80) is a legacy Complere doc
  convention; this stack serves the SPA on :3000 and API on /api (same origin),
- API direct: `http://127.0.0.1:8010/api/...`.

## 7. Follow-up (same day, user request)

- Playwright removed from the frontend for now:
  - deleted `frontend/playwright.config.ts`
  - deleted `frontend/tests/e2e/` (auth, dashboard, folio-checkout-gate,
    hotel-availability specs)
  - deleted `frontend/test-results/`
  - removed `@playwright/test` from `frontend/package.json` devDependencies
- `package-lock.json` not regenerated (no installs run).
- `@playwright/test` may still appear in `node_modules` and the lockfile until
  the next `npm install`.

## 8. API timeout fix (same day)

- **Symptom**: SPA showed "API timed out"; direct `/api/health/` answered but
  took 10-13s, `php artisan --version` took ~42s.
- **Cause**: `./backend-laravel:/app` Windows bind mount made PHP read the whole
  vendor tree over the slow Docker Desktop filesystem bridge on every boot.
- **Fix**: `docker-compose.dev.yml` now mounts a named volume
  `mtmtp_vendor:/app/vendor` (same pattern as nexro), so vendor lives on the
  container's native filesystem. Startup `composer install` keeps it in sync.
- **Result**: `php artisan --version` 42s -> 1.1s; `/api/health/` 10-13s ->
  0.26-0.44s; CSRF 0.43s; login 0.72s (200, `tenant_slug: lab`).
- **Note**: recreating the container is required after the compose change
  (`docker compose -f docker-compose.dev.yml up -d --force-recreate laravel`).

## 9. SPA dev mode (HMR) instead of static build

- **Decision**: dev uses the Nuxt dev server for instant HMR; the nginx static
  `spa` container is not rebuilt on every change.
- **How to run**:
  ```bash
  docker compose -f docker-compose.dev.yml stop spa   # free port 3000
  cd frontend && npm run dev                          # HMR on :3000
  ```
- **Fixes in `frontend/nuxt.config.ts`**:
  - `nitro.devProxy` targets now include the path (`/api`, `/sanctum`) because
    Nitro replaces the matched prefix with `target`; without it Laravel
    received `/health/` and returned 404.
  - `vite.server.allowedHosts` allows `*.mtmtp.local` / `*.localhost`, else
    Vite answers 403 "Blocked request. This host is not allowed".
  - Shared `backendUrl` const used by both devProxy and vite proxy.
- **Verified**: `/login` 200, `/api/health/` 200, `/sanctum/csrf-cookie` 204,
  `POST /api/auth/login` 200 (`tenant_slug: lab`) through `:3000`.


