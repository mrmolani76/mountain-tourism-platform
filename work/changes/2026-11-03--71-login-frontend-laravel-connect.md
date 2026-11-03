# Change Report: Module 1 login + frontend ↔ Laravel connect

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Wired Nuxt staff login to Laravel Core auth (session + CSRF), CORS/Sanctum for SPA cookies, nginx/compose proxy to `laravel`, and expanded DevPilotSeeder for PDF roles + lab.localhost.

## 2. Before
- Frontend called Django-style `/auth/login/` with `csrftoken`
- SPA nginx proxied `/api` → `web:8000` (Django)
- CORS `supports_credentials` false
- Compose stack was Django-centric

## 3. After
| Area | Change |
|------|--------|
| Frontend `$api` | Laravel `X-XSRF-TOKEN` + optional Django CSRF; `ensureLaravelCsrfCookie()` |
| Auth client | `/auth/login`, `/auth/me`, `/auth/logout` (trailing slash stripped) |
| Laravel | `statefulApi()`, CORS credentials, Core auth trailing-slash aliases |
| nginx | `/api` + `/sanctum` → `laravel:8000` |
| Compose | `laravel` service on `backend-laravel`; spa depends on it |
| Seeder | Super admin + 8 staff roles; domains `lab.localhost` + `localhost` |
| Env examples | Tenant host `lab.localhost`, central `saas.localhost` |

## 4. Reason
User: write migrations/login and integrate/connect frontend.

## 5. Files Affected
- `frontend/utils/api.ts`, `composables/api-clients/useApiClient.ts`, `useUserApiClient.ts`
- `frontend/types/user.ts`, `.env.example`, `docker/nginx-spa-proxy.conf`, `Dockerfile`
- `backend-laravel/bootstrap/app.php`, `config/cors.php`, `.env.example`
- `backend-laravel/Modules/Core/routes/api.php`
- `backend-laravel/database/seeders/DevPilotSeeder.php`
- `backend-laravel/Dockerfile`
- `docker-compose.dev.yml`

## 6. Verification / Status
- [ ] Not run on this machine (no Docker/composer here)
- [ ] Setup machine: migrate central + tenant, seed, open `http://lab.localhost:3000/login` as `owner@lab.test` / `password123`
