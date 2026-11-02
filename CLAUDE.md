# Mountain Tourism Complex Platform — Agent Rules

## Header Callouts
- **Before declaring done** → § Before Declaring Done + `verify-before-done` skill
- **When a test fails** → MUST run `triage-before-fix` first; never weaken tests to go green when app code is broken
- **Any change** → MUST write a Change Report under `work/changes/` (Rule 9)
- **Starting any work item** → MUST run `work-tracker`: `work/todo/` → `work/inprogress/` BEFORE edits; sync `PLAN.md` + `SUMMARY.md` (see § Work Tracking)
- **Confirmed bug** → MUST create `work/bugs/BUG-*.md` before fixing (`bug-tracker`)
- **Money** → Charge and Payment are append-only; void only (Rule 5)
- **Tenant** → Every business row carries `tenant_id`; no cross-tenant leaks (Rule 3)
- **Scope** → `document/Mountain_MVP_Scope_v1.pdf` is the contract; out-of-scope needs written agreement
- **Chat output** → Response Style table below. Chat = Hinglish, repo = English, no em-dash
- **Engineering standards** → `.cursor/rules/nexro-engineering.mdc` + `.opencode/rules.md` (keep twins in sync)

---

## Response Style

| Rule | Meaning |
|---|---|
| No over-explain | Get to the point. Skip extra background. |
| Simple words | Use easy words. Avoid heavy jargon. |
| Hinglish in chat | Chat replies use a Hindi + English mix. |
| English in the repo | Every file in the repo stays in English. |
| No long paragraphs | Break the answer into short pieces. |
| Points and tables | Use bullets or tables. |
| Proper spacing | Leave space between lines. Do not pack text together. |
| Crisp | Say only what is needed. |
| No em-dashes | Do not use an em-dash. Use a comma or a period. |

Apply this to every response: code, explanation, and discussion.

---

## Product

Web SaaS for mountain tourism complexes: hotel, chalets, huts, restaurants, rental, spa/sports, lifts, transport, weather. Guests get one folio across outlets. MVP proves one full winter or summer day for a pilot complex.

Contract scope: `document/Mountain_MVP_Scope_v1.pdf`.

---

## Stack

| Layer | Choice |
|-------|--------|
| Backend | Python, Django, Django REST Framework |
| Frontend | Nuxt, Vue.js, Vuetify |
| Layout | `backend/` (Django API), `frontend/` (Nuxt app) |
| Auth (MVP) | Email + password; owners/managers reset staff passwords; no email sending in MVP |
| Tenancy | Shared DB, row-level `tenant_id` on every business table (no subdomain tenancy in MVP) |
| Money | Append-only charges and payments; voids only |

Confirm frameworks from real project files when they exist. Do not invent another stack.

---

## Domain Vocabulary (use these names)

| Concept | Meaning |
|---------|---------|
| Tenant | One complex. Every table carries `tenant_id`. Staff belong to one tenant (known from login). |
| Building | Place with type, map position, altitude. Also a weather point. |
| Outlet | Business unit in a building: restaurant, shop, rental, spa, sports, transport, lifts. Own products, staff, reports. |
| Season | Winter, summer, or closed, with dates. Prices/menus/services can differ. |
| Stay | One visit in a room, chalet, or hut bed. |
| Board plan | Room only, B&B, half board, full board. |
| Folio | Bill of a stay. |
| Charge | One folio line. Always records outlet, tax, source. Never edited or deleted. |
| Payment | Cash or card, recorded by staff. Several per folio. Never edited or deleted. |
| Service / Slot | Bookable service and one time slot with capacity and staff. |
| Rental | One rented item from out to return. |
| Trip | One run of own transport: shuttle, taxi, or pickup. |
| Lift / Lift pass | Lift between two buildings + pass product. |
| Weather limit / Warning | Limit set by complex; warning raised on what it affects. |

---

## Roles (MVP)

| Role | Main jobs |
|------|-----------|
| Super admin | Create complexes and owner accounts; activate/deactivate |
| Owner | Settings, buildings, outlets, staff, prices, weather limits, reports |
| Manager | Assigned outlets: products, prices, voids, outlet reports |
| Front desk | Reservations, check-in/out, folios, payments |
| Cashier | Orders, payments, charge to room |
| Rental desk | Rent out, returns, damage fees |
| Service staff | Slots, bookings, schedule, guided outing headcount |
| Transport desk | Shuttles, taxi, pickups, vehicles, drivers |
| Lift operator | Lift status from phone |

Drivers do not log in in MVP (printed passenger list).

---

## Architecture Expectations

### Backend (`backend/`)
- Thin views/viewsets → services for business logic
- Models use the domain names above
- Serializers / DRF for API validation at the boundary
- Permissions and tenant scoping on every tenant-owned endpoint
- Migrations append-only: never edit a shipped migration; add a new one

Suggested layout (adjust only if repo already differs):
- `apps/` or domain packages for core, hotel, folio, pos, rental, services, transport, weather
- `tests/` with unit + API tests
- Audit for every charge, payment, and void

### Frontend (`frontend/`)
- Nuxt + Vue 3 + Vuetify
- Role-aware screens for desk workflows (tablet-friendly where scope requires)
- Call backend API only; no business money rules only on the client
- English UI copy unless product later adds i18n

### API
- Prefer consistent JSON envelopes once introduced; document them in code and keep them stable
- Public IDs should not leak other tenants' data
- Authorize on the server; never trust client role claims alone

---

## Non-Negotiable Rules

### Rule 1 — Scope contract
Build only what the MVP scope lists. If asked for out-of-scope work, say so and ask before coding.

### Rule 2 — Explicit git approval
`git commit`, `git push`, force push, and PR create happen ONLY after the user clearly approves in that exchange. When work is complete, ask: **"Commit aur push karun?"**

### Rule 3 — Tenant isolation
Every business query is scoped by `tenant_id`. Automated tests must prove two complexes cannot see each other's data. Super admin is the only cross-tenant operator surface.

### Rule 4 — No drive-by deps
Do not add new Python/npm packages without explicit user approval.

### Rule 5 — Money is append-only
A charge or payment is never edited or deleted. Void it with reason, user, and timestamp. Weather cancellation is a void with reason `weather`. Manager role for voids where scope requires it.

### Rule 6 — Domain names stay stable
Use the vocabulary table names in DB, code, and docs so humans and agents talk about the same things.

### Rule 7 — AuthZ on server
Check role and outlet access on the API. UI hiding is not enough.

### Rule 8 — Migrations immutable
Never edit a shipped migration. Schema gap → new migration file only.

### Rule 9 — Change report mandatory
Every completed change writes `work/changes/YYYY-MM-DD--<desc>.md` from `work/changes/CHANGE_REPORT_TEMPLATE.md`. No "done" without the report.

### Rule 10 — Work kanban
See § Work Tracking. Folder = status. Pickup before edits. Complete needs Completion Log + change report + PLAN/SUMMARY sync + ask commit/push. WIP = 1. Never delete work MDs. Validate with `bash scripts/check-work-kanban.sh`.

### Rule 11 — Bug reports first
Confirmed bug → create `work/bugs/BUG-<TYPE>-<NNN>-<short>.md` before fixing. Keep `work/bugs/INDEX.md` and `work/SUMMARY.md` in sync.

### Rule 12 — Tests protect money and tenancy
Prefer tests for: tenant isolation, folio posting, voids, board-included meals, rental double-book, capacity/slot conflicts, checkout zero-balance.

### Rule 13 — Secrets
Never commit `.env`, tokens, or passwords. Never print real secrets in chat or reports.

---

## Work Tracking — Kanban Folders

> Dashboard: `work/SUMMARY.md`. Flow detail: `work/README.md`. Skill: `work-tracker`. Handoff: `work/MEMORY.md`.

**Folder = status.** One MD per item.

| Location | Meaning |
|----------|---------|
| `work/todo/` | Planned (not picked up) |
| `work/inprogress/` | Active (WIP limit 1) |
| `work/completed/` | Finished |
| `work/PLAN.md` | Three tables only: Todo / In Progress / Completed — rows **move** between tables |
| `work/TEMPLATE.md` | Work-item format |
| `work/changes/` | Mandatory change reports |
| `work/bugs/` | Bug reports + INDEX |

**Flow:**
1. New → `work/todo/NN-title.md` + PLAN Todo row + SUMMARY
2. Before edits → `todo` → `inprogress`, `status: inprogress`, PLAN row → In Progress
3. Blocked → back to `todo` + Return to Pending Log + PLAN Todo
4. Complete → Completion Log → `completed` + PLAN Completed + `work/changes/YYYY-MM-DD--NN-*.md` + SUMMARY
5. Ask **"Commit aur push karun?"** — no commit until yes

If folder / frontmatter / PLAN disagree: trust **folder**, then repair frontmatter + PLAN. Run `bash scripts/check-work-kanban.sh`.

---

## Modules (MVP map)

| Code | Module |
|------|--------|
| A | Core and super admin |
| B | Hotel, chalets, huts |
| C | Guest folio and checkout |
| D | Restaurants, huts, shop |
| E | Equipment rental |
| F | Hotel services and activities |
| G | Transport and lifts |
| H | Weather |

Each module's "done when" in the scope PDF is the acceptance bar.

---

## Demo seed layout (altitudes are examples)

| Building | Altitude | What happens |
|----------|----------|--------------|
| Valley station | 1,050 m | Parking, tickets, rental, cable car lower end |
| Main hotel | 1,200 m | Rooms, reception, restaurant, sauna, spa, gym |
| Chalets | 1,250 m | Family chalets |
| Mid station | 1,800 m | Self-service, ski school, chairlift |
| Mountain hut | 2,100 m | Dorm beds, simple meals, guided hikes |
| Summit station | 2,600 m | Panorama restaurant, viewing, cable car top |

---

## Skills Index

Skills live in `.cursor/skills/` and are mirrored to `.opencode/skills/` and `.claude/skills/`.

| Skill | When |
|-------|------|
| `django-best-practices` | Backend Python/Django/DRF work |
| `nuxt-vuetify-best-practices` | Frontend Nuxt/Vue/Vuetify work |
| `coding-agent` | Implement / fix / refactor (code-only default) |
| `testing-agent` | Author tests (do not run by default) |
| `devops-agent` | Docker/CI/deploy config (config-only default) |
| `security-code-review` | Security review only, no app patches |
| `work-tracker` | Kanban pickup/complete under `work/` |
| `change-report` | After any change → `work/changes/` |
| `bug-tracker` | Confirmed bugs → `work/bugs/` |
| `triage-before-fix` | Red/flaky tests |
| `verify-before-done` | Before claiming done |

---

## Before Declaring Done

1. Every explicit user ask addressed
2. Files changed listed; no unrelated edits
3. Affected tests run when verification is allowed; report real results
4. Tenant + money + authz non-negotiables checked if touched
5. Change report written under `work/changes/`
6. Work MD moved to `completed/` if it was a tracked item
7. Asked: **"Commit aur push karun?"** (do not commit until yes)

---

## File Ownership

| File | Owner | Notes |
|------|-------|-------|
| `CLAUDE.md` | Humans | Single source of truth for project rules |
| `AGENTS.md` | Humans | Short pointer + skill index |
| `.cursor/rules/scope.mdc` | Rarely edit | Always points at `CLAUDE.md` |
| `.cursor/skills/*/SKILL.md` | Humans | Workflow / stack guidance |
| `.opencode/` and `.claude/` mirrors | Keep in sync with `.cursor/` | |
