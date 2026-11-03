#!/usr/bin/env bash
# Mountain Tourism Platform — complete Docker dev setup (one-shot).
# Mirrors the InventorySystem-docker bootstrap: build + up + migrate + seed.
# Usage:  ./setup-docker.sh
# Safe to re-run: up -d is idempotent; migrations/seeds are guarded.
set -euo pipefail

cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.dev.yml"

GREEN='\033[0;32m'; RED='\033[0;31m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()   { echo -e "  ${GREEN}●${NC} $*"; }
fail() { echo -e "  ${RED}●${NC} $*"; exit 1; }
info() { echo -e "  ${CYAN}…${NC} $*"; }

echo ""
echo -e "${CYAN}═══ mtmtp Docker dev setup ═══${NC}"
echo ""

# 1. Docker
if ! docker info >/dev/null 2>&1; then
  fail "Docker is not running or not reachable. Start Docker first."
fi
ok "Docker is running"

# 2. Hosts (tenant flow needs lab.local.mtmtp.local + saas.mtmtp.local)
if ! grep -q "lab.local.mtmtp.local" /etc/hosts 2>/dev/null; then
  info "Adding tenant hosts to /etc/hosts (needs sudo)..."
  echo "127.0.0.1 lab.local.mtmtp.local saas.mtmtp.local" | sudo tee -a /etc/hosts >/dev/null
fi
ok "Hosts present: lab.local.mtmtp.local, saas.mtmtp.local"

# 3. Build + start
info "Building and starting dev stack (mtmtp)..."
$COMPOSE up -d --build

# 4. Wait for DB
info "Waiting for database health..."
for i in $(seq 1 30); do
  if $COMPOSE exec -T db pg_isready -U mt_app -d mountain_tourism >/dev/null 2>&1; then
    ok "Database ready"; break
  fi
  [ "$i" -eq 30 ] && fail "Database did not become healthy"
  sleep 2
done

# 5. Wait for API health (migrations run on container start)
info "Waiting for API health (migrations run automatically)..."
for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8010/api/health/ 2>/dev/null || true)
  if [ "$code" = "200" ]; then ok "API healthy"; break; fi
  [ "$i" -eq 40 ] && fail "API not healthy; check: $COMPOSE logs laravel"
  sleep 2
done

# 6. Seed foundation (idempotent)
info "Seeding foundation data (idempotent)..."
$COMPOSE exec -T laravel php artisan db:seed --force 2>/dev/null || true
ok "Foundation seed attempted"

# 7. Show URLs
echo ""
echo -e "${CYAN}═══ Setup complete ═══${NC}"
echo "  SPA (tenant):  http://lab.local.mtmtp.local:3000/"
echo "  SPA (saas):    http://saas.mtmtp.local:3000/"
echo "  API:           http://127.0.0.1:8010/api/health/"
echo "  DB:            127.0.0.1:5433 (mt_app / devpass)"
echo ""
echo "Next:"
echo "  - Create super admin: $COMPOSE exec laravel php artisan tinker"
echo "  - Logs: $COMPOSE logs -f laravel"
echo "  - Stop: ./stop.sh   (keeps volumes)"
echo ""