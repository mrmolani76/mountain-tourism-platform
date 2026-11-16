---
title: Fix SPA API timeout — proxy target 127.0.0.1 (keep Host)
id: 95
priority: high
status: completed
milestone: none
module: A-core
area: frontend
effort: S
dependencies: 93
bug: none
created: 2026-11-16
updated: 2026-11-16
---

# Fix SPA API timeout — proxy target

## What to do (What)

Nuxt `/api` + `/sanctum` proxy must TCP-connect to Laravel on `127.0.0.1:8000` while keeping browser `Host` (`changeOrigin: false`) for tenant resolve. Code only.

## Acceptance Criteria (complete only when all ticked)

- [x] Proxy defaults + env example use 127.0.0.1:8000
- [x] changeOrigin stays false
- [x] Change report; PLAN + SUMMARY

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-16
- **What was done**: Proxy/env defaults → `127.0.0.1:8000`; CSRF timeout toast; docs snippets aligned
- **Change report**: `work/changes/2026-11-16--95-fix-spa-api-timeout-proxy-target.md`
- **Verify**: code-only; no serve/migrate
