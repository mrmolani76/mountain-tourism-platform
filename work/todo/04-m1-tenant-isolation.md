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

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-04

## What to do (What)
Tenant model + single isolation layer + automated test that two complexes cannot see each other’s data (PDF §5A done when).

## Current / Before
No Tenant / isolation layer.

## Expected / After
All tenant queries go through one mechanism; cross-tenant test green.

## Why
Module A acceptance; PDF §7 isolation in one place.

## Acceptance Criteria (complete only when all ticked)
- [ ] Tenant model with is_active
- [ ] tenant_id on tenant-owned base
- [ ] Single isolation layer implemented
- [ ] Client-sent tenant_id not trusted for authz
- [ ] Automated cross-tenant list+detail test passes
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` models, managers, tests
- `docs/DECISIONS.md` / ARCHITECTURE if needed
