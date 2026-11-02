---
title: Architecture note + core data model draft
id: 02
priority: high
status: todo
milestone: 1
module: A-core
area: docs
effort: M
dependencies: none
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Architecture note + core data model draft

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → section TODO-02

## What to do (What)
Fill architecture + data model for Module A (API-first). Record isolation and auth decisions. No frontend.

## Current / Before
`docs/ARCHITECTURE.md` has placeholders; isolation/auth undecided.

## Expected / After
Review-ready architecture note; entity list; DECISIONS rows for isolation + auth.

## Why
PDF Milestone 1 requires architecture note + data model; unlocks scaffold.

## Acceptance Criteria (complete only when all ticked)
- [ ] PDF §3–§5A + glossary + tech rules read
- [ ] `docs/ARCHITECTURE.md` filled (Django API layout)
- [ ] Isolation choice in `docs/DECISIONS.md`
- [ ] Auth style in `docs/DECISIONS.md`
- [ ] Entities listed: Tenant, Settings, Staff, OutletAccess, Building, Outlet, Season, Audit stub
- [ ] App package names proposed
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `docs/ARCHITECTURE.md`
- `docs/DECISIONS.md`

## Notes / Follow-ups
- Checklist detail: `work/phases/01-todos/A-core-and-super-admin.md`
