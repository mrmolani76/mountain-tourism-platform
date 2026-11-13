---
title: Split Pos tenant migrations — one table per file
id: 78
priority: high
status: completed
milestone: 3
module: D-pos
area: backend
effort: S
dependencies: 77
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Split Pos tenant migrations

## What to do (What)

Replace combined `2026_11_13_000001_create_pos_tables.php` with one migration per
`pos_*` table (Hotel module pattern). Migration not shipped yet — safe to replace.

## Acceptance Criteria

- [x] One migration file per pos table
- [x] Combined migration removed
- [x] Change report written
- [x] PLAN/SUMMARY updated

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-13
- **What was done**: Split into 10 single-table Pos tenant migrations
- **Files changed**: Pos `Database/migrations/tenant/*`
- **Verification**: N/A code-only (files present; migrate deferred)
- **Change report**: `work/changes/2026-11-13--78-split-pos-migrations.md`
- **PLAN / SUMMARY**: yes
