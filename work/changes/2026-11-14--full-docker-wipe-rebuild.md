# Change Report: Full Docker wipe + rebuild (post item 94)

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: full-docker-wipe-rebuild

## 1. Summary

Removed the entire mtmtp Docker stack (containers, network, volumes, images)
and rebuilt from `52d56b9` (includes item 94 strict central).

## 2. Before

- Stack running since the earlier fresh setup; central had `users` +
  `password_reset_tokens` left over from pre-item-94 migrate.

## 3. After

- `docker compose down -v --remove-orphans` + `docker rmi mtmtp-laravel mtmtp-spa`
  + stale volume `mountain-tourism-platform_mtmtp_pgdata` removed.
- `docker compose -f docker-compose.dev.yml up -d --build` fresh.
- Container start ran composer install, central `migrate` (root + Saas only),
  `tenants:provision-local lab --reset --with-saas-admin --host=lab.local.mtmtp.local`.

## 4. Reason

User: "fresh karo wapas complete docker sab remove karke".

## 5. Files Affected

- Runtime only (Docker). No source changes.

## 6. Verification / Status

- [x] Central `mountain_tourism`: 11 tables (strict allowlist, no `users`/`password_reset_tokens`)
- [x] Tenant `lab_local`: 66 tables, 8 fixture staff
- [x] `/api/health/` → 200
- [x] `POST /api/auth/login` (`owner@lab.test`) → 200, `tenant_slug: lab`
- [x] SPA `http://lab.local.mtmtp.local:3000/login` → 200
