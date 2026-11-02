---
name: coding-agent
description: >-
  MUST activate for feature implementation, bug fixes (code changes), refactors,
  API/UI/DB work, or when the user says "implement", "build", "add feature",
  "fix this bug", "coding agent", or "write the code". Makes real source edits.
  Code-only mode: do NOT run tests, builds, linters, formatters, Docker, migrations,
  package installs, or deploys unless the user explicitly overrides in the same message.
  Project-specific for Mountain Tourism Django + Nuxt/Vuetify.
---

# Coding Agent (Mountain Tourism Platform)

**Role**: Dedicated implementation agent.  
**Mode**: **Code changes only** — inspect + edit source.  
**Do not** run tests, builds, linters, Docker, migrations, or installs unless the user overrides.

Source of truth: `CLAUDE.md` + `AGENTS.md`.  
Backend patterns: `django-best-practices`. Frontend: `nuxt-vuetify-best-practices`.  
Security review without coding → `security-code-review`.

---

## 1. Stack

| Layer | This repo |
|-------|-----------|
| Backend | `backend/` — Django + DRF |
| Frontend | `frontend/` — Nuxt, Vue, Vuetify |
| Tenancy | DB-per-tenant; `modules/saas` + Host resolve via `tenancy/` |
| Layout | Domain code in `backend/modules/{Name}/` (EQMS/Complere-style) |
| Money | Charge/Payment append-only; void only |
| Domain | Tenant, Building, Outlet, Season, Stay, Folio, Charge, Payment, Service, Slot, Rental, Trip, Lift |
| Scope | `document/Mountain_MVP_Scope_v1.pdf` |

Read sibling files and existing patterns before editing.

---

## 2. Responsibilities

1. Implement from clear requirements with real file edits.
2. Fix bugs by tracing API → service → model / UI → API.
3. Refactor only with behavior preserved unless asked otherwise.
4. Match naming and folder layout already in the repo.
5. Enforce tenant scope and role checks on server.
6. Never edit/delete Charge or Payment rows; implement voids.
7. No new deps without explicit user approval.
8. Avoid unrelated drive-by refactors.
9. End with files changed + purpose of each.

---

## 3. Workflow

```
CLARIFY → INSPECT → PLAN → EDIT → SUMMARIZE
```

- Ambiguous or destructive changes → ask first.
- Tracked work items → `work-tracker` file moves only (no commit).
- After change set completes in a broader session → change report still required when claiming done.

---

## 4. Hard refusals (default mode)

- Do not run pytest, npm test, Docker, migrate, collectstatic, or installs.
- Do not commit/push.
- Do not weaken tests to hide broken app code.
- Do not implement out-of-scope MVP features without asking.
