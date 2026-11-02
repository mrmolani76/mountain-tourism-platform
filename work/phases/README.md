# Phase plans (from MVP PDF)

Source of truth: `document/Mountain_MVP_Scope_v1.pdf` (§5 modules, §6 hosting, §7 tech rules, §8 milestones, §11 handover).

Stack: Django + DRF + PostgreSQL (`docs/DECISIONS.md`). Nuxt/Vuetify is planned later.

## Current focus: backend only

Until the user says otherwise:

- Implement **Django + DRF API**, models, services, tests, OpenAPI, VPS/CI for the API.
- **Do not** scaffold or build Nuxt/Vuetify screens in this focus window.
- Phase MDs still list full MVP UI deliverables for later; mark UI packages as **deferred (frontend)**.
- Kanban items for now are backend/API/devops only.

## How this relates to kanban

| Layer | Role |
|-------|------|
| `work/phases/*.md` | Phase contract: scope, done-when, work packages |
| `work/todo|inprogress|completed/` | Executable work items (`milestone: N` in frontmatter) |
| `work/PLAN.md` | Live Todo / In Progress / Completed tables |

Rules:

1. Start a phase only when the previous phase **backend** acceptance is met (automated tests + API on VPS when deploy exists).
2. Turn work packages into `work/todo/NN-*.md` via `work-tracker`. Phase 1 backend packages seeded as **02–10**.
3. Prefix titles with milestone when helpful: `03-m1-django-scaffold`.
4. Do not implement §9 “After the MVP” items inside these phases.

## Index

| Phase | File | Weeks | Payment | Modules |
|-------|------|-------|---------|---------|
| 1 Foundation | [01-foundation.md](01-foundation.md) | 1–2 | 15% | A |
| 2 Hotel and folio | [02-hotel-and-folio.md](02-hotel-and-folio.md) | 3–5 | 25% | B + C |
| 3 POS, rental, services | [03-pos-rental-services.md](03-pos-rental-services.md) | 6–8 | 25% | D + E + F |
| 4 Transport, lifts, weather | [04-transport-lifts-weather.md](04-transport-lifts-weather.md) | 9–10 | 20% | G + H core |
| 5 Dashboard and handover | [05-dashboard-and-handover.md](05-dashboard-and-handover.md) | 11–12 | 15% | I + lobby/avalanche + §11 |

Lobby TV and avalanche are described under module H but are **Milestone 5** deliverables in §8. Phase 4 builds the weather engine; Phase 5 ships lobby + avalanche + handover (UI deferred while backend-only).
