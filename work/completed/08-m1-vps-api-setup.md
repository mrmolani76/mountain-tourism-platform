---
title: VPS setup script/guide for API
id: 08
priority: medium
status: completed
milestone: 1
module: A-core
area: devops
effort: M
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# VPS setup script/guide for API

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-08

## What to do (What)
Fresh VPS guide/script for API only: user, Postgres, Nginx, systemd, env, sslip.io/port. No Docker. Do not touch other sites.

## Current / Before
Hosting constraints in docs; no step-by-step setup for this repo.

## Expected / After
Rebuildable VPS setup doc/script for Django API.

## Why
PDF §6 + Milestone 1 server setup.

## Acceptance Criteria (complete only when all ticked)
- [ ] Linux user + Postgres + Nginx + systemd covered
- [ ] `.env` on server; no secrets in git
- [ ] No-Docker path explicit
- [ ] Domain-later = Nginx + env only noted
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `docs/VPS_SETUP.md` or `docs/HOSTING.md`
- `scripts/` if any
