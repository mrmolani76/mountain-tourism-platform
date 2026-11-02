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

## What to do (What)
Management command or seed that creates a demo tenant with §2 buildings/altitudes and sample outlets for API/dev. Full complex seed completes in Phase 5.

## Current / Before
No seed.

## Expected / After
Repeatable seed for Valley/Main hotel/Chalets/Mid/Hut/Summit with altitudes; useful for isolation and CRUD tests.

## Why
PDF §2 demo layout; handover full seed is Phase 5 — partial now unblocks backend work.

## Acceptance Criteria (complete only when all ticked)
- [ ] Seed creates §2 buildings with altitudes
- [ ] Idempotent or documented reset path
- [ ] Safe for local/dev only flags documented
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` management commands / fixtures

## Notes / Follow-ups
- Expand in Phase 5 handover seed.
