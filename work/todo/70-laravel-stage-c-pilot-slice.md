---
title: Laravel Stage C — pilot vertical slice (auth + envelope + 1 module list)
id: 70
priority: high
status: todo
milestone: none
module: A-core
area: backend
effort: L
dependencies: 69
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Laravel Stage C — pilot vertical slice

## What to do (What)
First Stage C slice per master plan §11 + audit inventory (docs/LARAVEL_SWITCH_AUDIT.md):
- Tenant-staff auth port: POST login / POST logout / GET me — session + tenant binding guard
  parity with StaffSessionAuthentication (audit §4), staff table/model per audit §3.
- DB-per-tenant middleware: host → central Domain lookup → active connection override.
- Envelope + page-number pagination parity (meta keys per audit §2).
- ONE representative list endpoint (e.g. api/buildings owner-protected) ported end-to-end.
- Manual verification checklist per contract (frontend call shapes).

## Current / After
Before: backend-laravel/ boots, health + envelope macros exist (item 69).
After: auth + tenant middleware + pagination parity + buildings slice; audit rows #10–#13, #17
moved to In progress/Verified as evidence.

## Why
Master plan Stage C; one slice at a time (principle 6).

## Acceptance Criteria
- [ ] Middleware resolves host→tenant DB (404 on unknown/inactive, mirrors current behaviour)
- [ ] Login/me/logout contract identical (401 vs 403 semantics per audit)
- [ ] Pagination meta parity
- [ ] Buildings CRUD owner-gated ported
- [ ] Audit inventory updated row-by-row
- [ ] NO changes to Django backend; NO tests in CI
