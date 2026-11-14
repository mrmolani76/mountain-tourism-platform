#!/usr/bin/env bash
# Sync Mountain Tourism local hosts block (Nexro-style hosts:sync without requiring DB).
#
# Usage:
#   bash scripts/mtmp-hosts-sync.sh --dry-run
#   sudo bash scripts/mtmp-hosts-sync.sh
#   sudo bash scripts/mtmp-hosts-sync.sh --remove-all
#   bash scripts/mtmp-hosts-sync.sh --extra demo.mtmp.local,lab.mtmp.local
#
# Prefer `cd backend-laravel && sudo php artisan hosts:sync` after DB has domains.
#
set -euo pipefail

HOSTS_FILE="${HOSTS_PATH:-/etc/hosts}"
IP="${HOSTS_IP:-127.0.0.1}"
BASE_DOMAIN="${APP_BASE_DOMAIN:-mtmp.local}"
DRY_RUN=0
REMOVE_ALL=0
EXTRA=""

BLOCK_START='# ── Mountain Tourism (auto-managed) ──'
BLOCK_END='# ── /Mountain Tourism ──'

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run) DRY_RUN=1; shift ;;
    --remove-all) REMOVE_ALL=1; shift ;;
    --ip=*) IP="${1#*=}"; shift ;;
    --extra=*) EXTRA="${1#*=}"; shift ;;
    --extra) EXTRA="${2:-}"; shift 2 ;;
    -h|--help)
      sed -n '2,12p' "$0"
      exit 0
      ;;
    *) echo "Unknown arg: $1" >&2; exit 1 ;;
  esac
done

if [[ ! -f "$HOSTS_FILE" ]]; then
  echo "Hosts file not found: $HOSTS_FILE" >&2
  exit 1
fi

DOMAINS=(
  "$BASE_DOMAIN"
  "saas.$BASE_DOMAIN"
  "demo.$BASE_DOMAIN"
  "lab.$BASE_DOMAIN"
)

if [[ -n "$EXTRA" ]]; then
  IFS=',' read -r -a MORE <<< "$EXTRA"
  for d in "${MORE[@]}"; do
    d="$(echo "$d" | xargs)"
    [[ -n "$d" ]] && DOMAINS+=("$d")
  done
fi

# Unique
mapfile -t DOMAINS < <(printf '%s\n' "${DOMAINS[@]}" | awk 'NF && !seen[$0]++')

remove_block() {
  # Remove previous managed block
  awk -v start="$BLOCK_START" -v end="$BLOCK_END" '
    $0 == start { skip=1; next }
    $0 == end { skip=0; next }
    !skip { print }
  ' "$HOSTS_FILE"
}

if [[ "$REMOVE_ALL" -eq 1 ]]; then
  if [[ "$DRY_RUN" -eq 1 ]]; then
    echo "[DRY RUN] Would remove managed Mountain Tourism block from $HOSTS_FILE"
    exit 0
  fi
  if [[ ! -w "$HOSTS_FILE" ]]; then
    echo "Not writable: $HOSTS_FILE (use sudo)" >&2
    exit 1
  fi
  tmp="$(mktemp)"
  remove_block > "$tmp"
  cat "$tmp" > "$HOSTS_FILE"
  rm -f "$tmp"
  echo "Removed Mountain Tourism managed block."
  exit 0
fi

BLOCK="$BLOCK_START"$'\n'
for d in "${DOMAINS[@]}"; do
  BLOCK+="${IP}"$'\t'"${d}"$'\n'
done
BLOCK+="$BLOCK_END"

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "[DRY RUN] Would write to $HOSTS_FILE:"
  echo "$BLOCK"
  exit 0
fi

if [[ ! -w "$HOSTS_FILE" ]]; then
  echo "Not writable: $HOSTS_FILE" >&2
  echo "Run: sudo bash scripts/mtmp-hosts-sync.sh" >&2
  exit 1
fi

tmp="$(mktemp)"
remove_block > "$tmp"
printf '\n%s\n' "$BLOCK" >> "$tmp"
cat "$tmp" > "$HOSTS_FILE"
rm -f "$tmp"

echo "Synced ${#DOMAINS[@]} domain(s) to $HOSTS_FILE"
printf '  %s\n' "${DOMAINS[@]}"
