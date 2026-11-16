---
title: Core menu UI+backend parity + allow fullstack delivery
id: 98
priority: high
status: completed
milestone: none
module: A-core
area: backend
effort: M
dependencies: 97
bug: none
created: 2026-11-16
updated: 2026-11-16
---

# Core menu UI+backend parity + fullstack rule

## Acceptance Criteria

- [x] CLAUDE/AGENTS/PLAN/MEMORY/DECISIONS updated for fullstack
- [x] Core menu UI+API gaps fixed
- [x] Change report

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-16
- **What was done**: Fullstack rule; Staff reset password; preference no-op; DELETE 204 fix; owner perms
- **Change report**: `work/changes/2026-11-16--98-core-menu-fullstack-rule.md`

## Core menu check (UI ↔ API)

| Menu | UI | Backend |
|------|-----|---------|
| Staff | `/users`, form, view + reset | `users/*`, `user/*`, `auth/reset-password` |
| Buildings | `/buildings` ResourceCrud | `buildings` + `buildings/list` |
| Outlets | `/outlets` ResourceCrud | `outlets` + `outlets/list` |
| Seasons | `/seasons` ResourceCrud | `seasons` + `seasons/list` |
| Settings | `/general-settings` | `GET/PATCH settings` |
