---
title: VPS setup script/guide for API
id: 08
priority: medium
status: todo
milestone: 1
module: infra
area: devops
effort: M
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# VPS setup script/guide for API

## What to do (What)
Document/script fresh VPS setup for **backend only**: Linux user, Postgres DB/user, Nginx to gunicorn/uvicorn, systemd, `.env`, sslip.io or port. No Docker required. Do not touch other sites on the VPS.

## Current / Before
`docs/HOSTING.md` constraints exist; no step-by-step setup script for this repo.

## Expected / After
`docs/` or `scripts/` guide that rebuilds API host from zero; matches PDF §6.

## Why
Milestone 1 server setup deliverable; client must rebuild without us.

## Acceptance Criteria (complete only when all ticked)
- [ ] Setup guide/script covers user, DB, Nginx, systemd, env
- [ ] Explicit no-Docker MVP path
- [ ] Domain later = Nginx + env only, noted
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `docs/HOSTING.md` or `docs/VPS_SETUP.md`
- `scripts/` if any

## Notes / Follow-ups
- Frontend static deploy later.
