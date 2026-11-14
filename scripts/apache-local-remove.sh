#!/usr/bin/env bash
# Remove Mountain Tourism Apache local vhosts from THIS machine only.
# Does not delete repo docs or Docker files.
#
# Usage:
#   sudo bash scripts/apache-local-remove.sh
#
set -euo pipefail

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash scripts/apache-local-remove.sh" >&2
  exit 1
fi

a2dissite mtmtp-lab.localhost.conf mtmtp-saas.localhost.conf 2>/dev/null || true
rm -f /etc/apache2/sites-available/mtmtp-lab.localhost.conf
rm -f /etc/apache2/sites-enabled/mtmtp-lab.localhost.conf
rm -f /etc/apache2/sites-available/mtmtp-saas.localhost.conf
rm -f /etc/apache2/sites-enabled/mtmtp-saas.localhost.conf

if command -v apache2ctl >/dev/null; then
  apache2ctl configtest
  systemctl reload apache2 2>/dev/null || service apache2 reload 2>/dev/null || true
fi

# Only strip lines that mention these hosts (Complere hosts untouched)
if [[ -f /etc/hosts ]]; then
  sed -i '/saas\.localhost/d;/lab\.localhost/d' /etc/hosts
  echo "Removed lab.localhost / saas.localhost lines from /etc/hosts (if present)"
fi

echo "mtmtp Apache sites removed from this machine."
