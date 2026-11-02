---
title: Foundation seed — §2 demo buildings (partial)
id: 10
priority: medium
status: todo
milestone: 1
module: A-core
area: backend
effort: S
dependencies: 06
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Foundation seed — §2 demo buildings (partial)

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-10

## What to do (What)
Seed demo tenant with PDF §2 buildings/altitudes + minimal outlets. Full complex seed remains Phase 5.

## Current / Before
No seed command.

## Expected / After
Repeatable seed for isolation/CRUD demos.

## Why
PDF §2 layout; unblocks Module A testing.

## Acceptance Criteria (complete only when all ticked)
- [ ] Valley 1050, Hotel 1200, Chalets 1250, Mid 1800, Hut 2100, Summit 2600
- [ ] Sample outlets created
- [ ] Idempotent or reset documented
- [ ] Dev-only safety noted
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` management command / fixtures
