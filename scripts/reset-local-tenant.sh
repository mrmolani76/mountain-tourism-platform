#!/usr/bin/env bash
#
# reset-local-tenant.sh — local multi-tenant provision (eQMS resetLocalDatabase.sh pattern).
#
# eQMS (complere/resetLocalDatabase.sh):
#   Tenant host = "{url_prefix}.{APP_ENV}.complere.local"
#   Tenant DB   = "{url_prefix}_{APP_ENV}"
#   Provision via central /saas/create + queue drain
#
# Mountain (Module 1, this script):
#   Tenant host = "{slug}.localhost"          (dev shorthand)
#   Tenant DB   = "{slug}_local"
#   Provision via `php artisan tenants:provision-local` (no AWS, no queue yet)
#
# USAGE (run from repo root OR backend-laravel/):
#   ./scripts/reset-local-tenant.sh              # provision lab (+ saas admin)
#   ./scripts/reset-local-tenant.sh lab          # same, explicit
#   ./scripts/reset-local-tenant.sh lab --reset  # drop lab_local + recreate + seed
#   ./scripts/reset-local-tenant.sh demo --reset
#
# Requires: APP_ENV=local, Postgres reachable, central migrations already applied.
# Does NOT run on the agent host by default — use the setup machine.
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LARAVEL_DIR="${ROOT}/backend-laravel"
SLUG="${1:-lab}"
shift || true
EXTRA_ARGS=("$@")

if [[ ! -d "${LARAVEL_DIR}" ]]; then
  echo "Error: backend-laravel/ not found at ${LARAVEL_DIR}"
  exit 1
fi

# ---------------------------------------------------------------------------
# Docker vs host (eQMS detects complere_app; we detect mtmtp_laravel)
# ---------------------------------------------------------------------------
USE_DOCKER=false
if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "mtmtp_laravel"; then
  USE_DOCKER=true
fi
echo "Environment: $([ "${USE_DOCKER}" = true ] && echo "Docker (mtmtp_laravel)" || echo "Host artisan")"

run_artisan() {
  if [ "${USE_DOCKER}" = true ]; then
    docker exec -i mtmtp_laravel php artisan "$@"
  else
    (cd "${LARAVEL_DIR}" && php artisan "$@")
  fi
}

# Guard: local only (mirrors eQMS APP_ENV check)
APP_ENV_VALUE="$(run_artisan tinker --execute="echo config('app.env');" 2>/dev/null | tail -n1 | tr -d '\r' || true)"
if [ "${APP_ENV_VALUE}" != "local" ] && [ "${USE_DOCKER}" = false ]; then
  # When docker compose sets APP_ENV=local in the container, prefer that path.
  if [ ! -f "${LARAVEL_DIR}/.env" ] || ! grep -qE '^APP_ENV=local' "${LARAVEL_DIR}/.env"; then
    echo "Warning: could not confirm APP_ENV=local from host .env; artisan command will refuse if not local."
  fi
fi

echo "Provisioning tenant slug='${SLUG}' ..."
run_artisan tenants:provision-local "${SLUG}" --with-saas-admin "${EXTRA_ARGS[@]}"

echo ""
echo "Login smoke (setup machine):"
echo "  1. Ensure /etc/hosts has: 127.0.0.1  ${SLUG}.localhost saas.localhost"
echo "  2. Open http://${SLUG}.localhost:3000/login"
echo "  3. owner@${SLUG}.test / password123"
echo "  4. DevTools → Network: POST /api/auth/login → 200, then GET /api/auth/me → 200"
echo ""
echo "Checklist MD: frontend/docs/LOGIN_TEST_CHECKLIST.md"
