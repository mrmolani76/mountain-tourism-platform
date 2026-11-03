#!/bin/bash
# Mountain Tourism Platform — dev stack status (containers + health).
cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.dev.yml"

GREEN='\033[0;32m'; RED='\033[0;31m'; CYAN='\033[0;36m'; NC='\033[0m'

echo ""
echo -e "${CYAN}═══ mtmtp dev stack ═══${NC}"
echo ""
$COMPOSE ps
echo ""

check() { # name url expected
  code=$(curl -s -o /dev/null -w "%{http_code}" "$2" 2>/dev/null || true)
  if [ "$code" = "$3" ]; then
    echo -e "  ${GREEN}●${NC} $1 ($2 → $code)"
  else
    echo -e "  ${RED}●${NC} $1 ($2 → ${code:-unreachable})"
  fi
}

check "API health" "http://127.0.0.1:8010/api/health/" "200"
check "SPA"        "http://127.0.0.1:3000/"            "200"
echo ""
echo "Note: inventory stack (inventory_*) is a separate project; untouched by these scripts."
