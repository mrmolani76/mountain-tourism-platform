#!/bin/bash
# Mountain Tourism Platform — Docker dev launcher (Linux/macOS).
# Builds (if needed) and starts the dev stack. KEEPS volumes.
set -e

cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.dev.yml"

if ! docker info >/dev/null 2>&1; then
  echo "[FAIL] Docker is not running or not reachable. Start Docker first."
  exit 1
fi

echo "[SETUP] Building and starting dev stack (mtmtp)..."
$COMPOSE up -d --build

echo ""
echo "Waiting for web health (max ~60s)..."
for i in $(seq 1 30); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8010/api/health/ 2>/dev/null || true)
  if [ "$code" = "200" ]; then
    echo "[  OK ] API healthy: http://127.0.0.1:8010/api/health/"
    break
  fi
  [ "$i" -eq 30 ] && echo "[WARN] API not healthy yet; check: $COMPOSE logs web"
  sleep 2
done

echo ""
echo "  SPA:  http://127.0.0.1:3000/"
echo "  API:  http://127.0.0.1:8010/api/health/"
echo "  Logs: $COMPOSE logs -f web"
echo "  Stop: ./stop.sh   (keeps volumes)"
