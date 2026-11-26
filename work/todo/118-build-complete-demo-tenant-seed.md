---
title: Build complete MVP demo tenant seed
id: 118
priority: medium
status: todo
milestone: 5
module: A-core
area: backend
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Build complete MVP demo tenant seed

## What to do (What)
Extend the limited foundation seed into an idempotent demo-tenant seed that supports end-to-end MVP acceptance workflows.

## Current / Before
`FoundationSeeder` creates the six buildings and a limited set of outlets. The full demo complex still lacks seed coverage for hotel inventory/prices, POS, rental, services, transport, weather and representative folio activity.

## Expected / After
A documented, repeatable tenant seed creates a coherent demo complex across Modules A-H, with relationships and sample data suitable for owner reports and module acceptance checks.

## Why
The MVP handover checklist requires a script that creates the demo complex described in §2.

## Acceptance Criteria (complete only when all ticked)
- [ ] Seed buildings/outlets and representative hotel rooms, hut beds, seasons, rates, board plans and reservations.
- [ ] Seed POS products, rental inventory/prices, services/slots, transport routes/trips/lifts and weather points/limits.
- [ ] Ensure all created records use valid tenant-local relationships and no cross-tenant access.
- [ ] Keep seeding idempotent and avoid duplicating money transactions on repeated runs.
- [ ] Add a safe test of seed completeness and update `docs/SEED_COMPLEX.md` with exact run instructions.
- [ ] `work/changes/YYYY-MM-DD--118-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Core/Database/Seeders/`
- `backend-laravel/Modules/`
- `docs/SEED_COMPLEX.md`
- `backend-laravel/tests/`

## Notes / Follow-ups
- Never seed production tenant records.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
