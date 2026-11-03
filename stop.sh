#!/bin/bash
# Mountain Tourism Platform — stop dev stack. KEEPS volumes (no down -v).
set -e

cd "$(dirname "$0")"
docker compose -f docker-compose.dev.yml down

echo "[  OK ] Dev stack stopped. Volume mtmtp_mtmtp_pgdata preserved."
echo "         To wipe the dev DB too (DATA LOSS): docker volume rm mtmtp_mtmtp_pgdata"
