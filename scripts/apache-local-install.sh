#!/usr/bin/env bash
# Install Mountain Tourism Apache local vhosts on THIS machine (Complere-style).
# Run on the host with sudo. Agent containers often cannot write /etc.
#
# Usage:
#   sudo bash scripts/apache-local-install.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
AVAIL="/etc/apache2/sites-available"
SRC="$ROOT/docs/apache"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash scripts/apache-local-install.sh" >&2
  exit 1
fi

if [[ ! -d /etc/apache2 ]]; then
  echo "Apache2 not found under /etc/apache2" >&2
  exit 1
fi

# /etc/hosts
if ! grep -qE '(^|[[:space:]])lab\.localhost($|[[:space:]])' /etc/hosts; then
  echo "127.0.0.1 saas.localhost lab.localhost" >> /etc/hosts
  echo "Added lab.localhost saas.localhost to /etc/hosts"
else
  if ! grep -qE '(^|[[:space:]])saas\.localhost($|[[:space:]])' /etc/hosts; then
    echo "127.0.0.1 saas.localhost" >> /etc/hosts
    echo "Added saas.localhost to /etc/hosts"
  else
    echo "hosts already contain lab/saas.localhost"
  fi
fi

a2enmod ssl rewrite headers proxy proxy_http alias >/dev/null

cp -f "$SRC/mtmtp-lab.localhost.conf" "$AVAIL/mtmtp-lab.localhost.conf"
cp -f "$SRC/mtmtp-saas.localhost.conf" "$AVAIL/mtmtp-saas.localhost.conf"

a2ensite mtmtp-lab.localhost.conf mtmtp-saas.localhost.conf
apache2ctl configtest
systemctl reload apache2 2>/dev/null || service apache2 reload

echo
echo "Installed:"
echo "  http://lab.localhost/   → Nuxt :3000 + /api → Laravel :8000"
echo "  http://saas.localhost/  → same proxy pattern (central host)"
echo
echo "Start apps (separate terminals):"
echo "  cd $ROOT/backend-laravel && php artisan serve --host=127.0.0.1 --port=8000"
echo "  cd $ROOT/frontend && npm run dev -- --host 127.0.0.1 --port 3000"
echo
echo "Remove later: sudo bash $ROOT/scripts/apache-local-remove.sh"
