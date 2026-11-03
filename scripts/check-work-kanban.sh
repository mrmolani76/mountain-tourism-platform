#!/usr/bin/env bash
# Validate work/ kanban: folder matches frontmatter status; PLAN tables mention each ID once.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
ERR=0

status_of() {
  local f="$1"
  grep -E '^status:' "$f" | head -1 | awk '{print $2}' | tr -d '\r'
}

id_of() {
  local f="$1" base
  base="$(basename "$f")"
  echo "${base%%-*}"
}

check_dir() {
  local dir="$1" expect="$2"
  local f st id
  shopt -s nullglob
  for f in "$dir"/*.md; do
    base="$(basename "$f")"
    [[ "$base" == .* ]] && continue
    # Only NN-title.md kanban items (skip design/README notes in the same folder)
    if [[ ! "$base" =~ ^[0-9]+- ]]; then
      continue
    fi
    st="$(status_of "$f" || true)"
    id="$(id_of "$f")"
    if [[ -z "$st" ]]; then
      echo "FAIL: missing status frontmatter: $f"
      ERR=1
      continue
    fi
    if [[ "$st" != "$expect" ]]; then
      echo "FAIL: $f status='$st' but folder expects '$expect'"
      ERR=1
    fi
    # Count ID mentions across PLAN sections (rough: whole file should mention ID)
    if ! grep -qE "\|[[:space:]]*${id}[[:space:]]*\|" work/PLAN.md; then
      echo "WARN: ID $id not found as a PLAN.md table row (| $id |)"
    fi
  done
}

check_dir work/todo todo
check_dir work/inprogress inprogress
check_dir work/completed completed

# WIP limit
shopt -s nullglob
inprog=(work/inprogress/*.md)
if ((${#inprog[@]} > 1)); then
  echo "FAIL: WIP limit exceeded (${#inprog[@]} files in work/inprogress/)"
  ERR=1
fi

# Empty placeholder rows in PLAN are ok; duplicate ID rows are not
dupes="$(grep -oE '\|[[:space:]]*[0-9]{2,}[[:space:]]*\|' work/PLAN.md | tr -d '|' | tr -d ' ' | sort | uniq -d || true)"
if [[ -n "$dupes" ]]; then
  echo "FAIL: duplicate IDs in PLAN.md:"
  echo "$dupes"
  ERR=1
fi

if [[ "$ERR" -eq 0 ]]; then
  echo "OK: work kanban checks passed"
  exit 0
fi
echo "FAILED: work kanban has issues"
exit 1
