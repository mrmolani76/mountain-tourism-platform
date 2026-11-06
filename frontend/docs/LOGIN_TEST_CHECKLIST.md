# Login test checklist (setup machine)

Use after provisioning a local tenant. Script: `./scripts/reset-local-tenant.sh lab --reset`

## 0. Preconditions

- [ ] `docker compose -f docker-compose.dev.yml up -d` (or host Postgres + artisan serve)
- [ ] Central migrate done (`php artisan migrate --force`)
- [ ] Tenant provisioned: `./scripts/reset-local-tenant.sh lab --reset`
- [ ] `/etc/hosts` has `127.0.0.1 lab.localhost saas.localhost`

## 1. Host + page

- [ ] Open `http://lab.localhost:3000/login` (not bare `127.0.0.1`)
- [ ] Login form renders (email + password)

## 2. Happy path (owner)

- [ ] Email `owner@lab.test` / password `password123`
- [ ] Submit → lands on `/` (or `?to=` target), not stuck on `/login`
- [ ] Hard refresh → still logged in (session cookie survives)

## 3. Network (DevTools)

- [ ] `GET /sanctum/csrf-cookie` → 204/204-ish / Set-Cookie `XSRF-TOKEN`
- [ ] `POST /api/auth/login` → **200** + staff JSON (`role_code: owner`)
- [ ] `GET /api/auth/me` → **200** (middleware probe)

## 4. Negative cases

- [ ] Wrong password → 401, stay on login / toast
- [ ] `http://saas.localhost:3000/login` staff login → expect fail / “tenant host” (staff is tenant-only)
- [ ] After logout → `/api/auth/me` → 401

## 5. Other fixture roles (optional)

Password for all: `password123`

| Email | Role |
|-------|------|
| `manager@lab.test` | manager |
| `desk@lab.test` | front_desk |
| `cashier@lab.test` | cashier |
| `rental@lab.test` | rental_desk |
| `service@lab.test` | service_staff |
| `transport@lab.test` | transport_desk |
| `lift@lab.test` | lift_operator |

- [ ] At least one non-owner role can login

## 6. Pass / fail note

| Date | Machine | Result | Notes |
|------|---------|--------|-------|
| | | | |

See also: `Mountain_SPA_API_AUTH.md`, `LOCAL_SETUP.md`
