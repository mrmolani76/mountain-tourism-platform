---
title: Tenant isolation layer + cross-tenant test
id: 04
priority: high
status: todo
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Tenant isolation layer + cross-tenant test

## What to do (What)
Implement **one** tenant scoping mechanism (scoped manager/repository or Postgres RLS) used by all tenant-owned queries. Add automated test: two tenants cannot read each other's data.

## Current / Before
No Tenant model or isolation layer.

## Expected / After
`tenant_id` on business models; single isolation path; pytest/Django test proves no cross-tenant leak (PDF §5A done-when for foundation).

## Why
PDF §7: tenant isolation in one place. Milestone 1 acceptance.

## Acceptance Criteria (complete only when all ticked)
- [ ] Isolation mechanism documented in ARCHITECTURE/DECISIONS
- [ ] Controllers/viewsets do not ad-hoc filter inconsistently
- [ ] Automated cross-tenant test passes
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` models, managers, middleware/permissions
- `backend/` tests
- `docs/DECISIONS.md`

## Notes / Follow-ups
- Super admin cross-tenant ops only on dedicated endpoints.
