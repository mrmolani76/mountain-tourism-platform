# Laravel Backend Switch — Master Plan

**Status:** Proposed implementation baseline
**Backend:** Laravel
**Database:** PostgreSQL
**Frontend:** Existing Vue/Nuxt unless the client separately approves Next.js.
**Previous backend:** Django + Django REST Framework (DRF)

## 1. Purpose

This document records the decision to switch the Mountain Tourism Platform backend from
Django/DRF to Laravel. It defines the target architecture, migration process, implementation
standards, deployment direction, risks, and completion criteria. It is a master plan; it does
not claim the migration is implemented.

## 2. Technology decision

| Layer | Decision |
|---|---|
| Backend | Laravel |
| Language | PHP |
| Database | PostgreSQL |
| API | REST, versioned under `/api/v1` |
| Authentication | Evaluate Sanctum after auditing current auth |
| Frontend | Existing Vue/Nuxt, pending client confirmation |
| Maps | Leaflet or MapLibre (per scope) |
| Weather | MET Norway (per scope) |
| Server | Ubuntu 24.04 LTS + Nginx, PHP-FPM |
| Source control | GitHub |
| CI/CD | GitHub Actions, updated for Laravel |

### Why Laravel
- Explicitly allowed in the client's stated backend preference (NestJS or Laravel).
- Team has practical Laravel experience.
- Routing, middleware, validation, ORM, migrations, auth integration, queues, notifications, storage are provided.
- Reduced framework overhead; future AI/ML can be a separate Python service.

### Frontend is a separate decision
Keep Vue/Nuxt until client confirms otherwise. Any frontend framework change is a separate approved decision.

## 3. Scope

**Included:** replace Django/DRF functionality with Laravel; preserve PostgreSQL; audit existing APIs/models/permissions/business rules; recreate approved behavior; compatible API contracts for current frontend; safe schema/data migration plan; update local dev, CI, deployment config.

**Not automatically included:** Next.js rewrite; changing DB tech; unapproved modules; changed business rules during porting; deleting Django before parity + cutover acceptance; production deployment/destructive migrations without approval; removing tests (tests stay in repo for local/manual verification).

## 4. Migration principles

1. Audit before coding (see `docs/LARAVEL_SWITCH_AUDIT.md`).
2. Preserve approved behavior before improvements.
3. Preserve routes, request fields, response shapes, status behavior where practical.
4. Back up data before schema/data changes.
5. Map every API to a Laravel implementation or mark explicitly out of scope.
6. One module/vertical slice at a time.
7. Keep production stable until cutover approved.
8. Record assumptions, deviations, client approvals.

## 5. Phase 0 — Existing project audit

Deliverables completed in work item 68 → **`docs/LARAVEL_SWITCH_AUDIT.md`** (module list, API
inventory of 45 endpoints, DB schema inventory, auth/permission matrix, integrations, storage,
tech debt, migration risks).

## 6. Phase 1 — Laravel foundation
Runtime/extension verification, composer create-project in a separate `backend-laravel/` directory, PostgreSQL env config, `.env` git-excluded, CORS, logging, health endpoint, local setup docs.

## 7. Recommended Laravel architecture

```
backend-laravel/
├── app/
│   ├── Http/Controllers/Api/V1/
│   ├── Http/Middleware/
│   ├── Http/Requests/
│   ├── Http/Resources/
│   ├── Models/  Policies/  Services/  Actions/
│   ├── Enums/  Jobs/  Exceptions/  Support/
├── routes/api.php
├── database/{factories,migrations,seeders}
├── storage/  tests/  .env.example
```
Controllers receive validated input → call service/action → return resource. No business logic dumped in controllers. Layers created only when a use case exists.

## 8. API standards
- Versioning: `/api/v1` prefix (target state).
- FastAPI → Laravel note: **the existing frontend expects the current DRF envelope** `{success, data, error, meta}` — preserve it until the client approves a change (see audit §2).
- Status codes: 200/201/204/400/401/403/404/409/422/429/500 per master plan table.
- Form Requests for validation; API Resources for serialization; server-side authorization enforced.

## 9. Authentication and authorization
- Audit first (done): sessions with tenant binding, cross-host replay guard, staff-vs-SaaS session separation.
- Laravel default candidate: Sanctum (SPA cookie or token), subject to confirming domain/cookie needs.
- Policies/Gates for authorization; roles/permissions extracted from audit matrix, never invented.

## 10. PostgreSQL and data migration
Backup → inspect schema → map tables to models → Laravel migrations → compare → test on copy → verify row counts → approval before production changes. Never destructive without approval; preserve keys/relationships/timestamps/history; review decimal precision (implemented schema uses DECIMAL(12,2)/(10,2) — integer minor units remain a decision conflict to resolve with client).

## 11. Business module implementation
One module record per confirmed module (models, routes, controllers, form requests, policies, resources, frontend screens, acceptance). Vertical slices.

## 12. External integrations
Maps: frontend renders; backend validates/normalizes location data. Weather: MET Norway via a Laravel service (timeouts, caching, attribution) once in scope.

## 13. Files and media
No uploads exist today (audit §6). Add filesystem config when uploads enter scope.

## 14. Queues, notifications, scheduling
No jobs exist today. Add queues/scheduler only with demonstrated need.

## 15. Frontend integration
Inventory current API calls; preserve request/response contracts; repoint API base; verify CORS/cookies/pagination/uploads/errors before removing any Django assumption.

## 16. Testing and quality policy
Tests stay in the repository. **CI must not execute automated test suites.** CI runs non-test checks only (composer validation, php -l / lint, static analysis, no `php artisan test`/PHPUnit/Pest). Local/manual verification covers boot, routes, migration status, auth, validation, contracts, CRUD, integrations, regression.

## 17. CI/CD and deployment
Deploy only from the exact approved commit artifact (never stale server copies). Nginx serves `public/`, PHP-FPM, storage + bootstrap/cache permissions, secure env, controlled migrations only after backup, health check + rollback. Keep Django available until cutover accepted.

## 18. Environment configuration
Separate local/staging/production. Commit `.env.example` only; never real credentials; per-environment APP_KEY; debug off in production.

## 19. Execution roadmap
- **Stage A — Preparation:** confirm decisions; audit (done, item 68); inventories; backups; migration/rollback plan.
- **Stage B — Laravel foundation:** create app; verify PHP runtime; configure PostgreSQL; versioning/CORS; health endpoint; logging; auth foundation decision.
- **Stage C — Module implementation:** vertical slices per confirmed module.
- **Stage D — Data and integrations:** schema validation; test data migration on copy; verify auth/permissions; maps/weather in scope.
- **Stage E — Release readiness:** security review; env review; backup/restore; deploy/rollback verification; client acceptance.
- **Stage F — Cutover:** approved schedule; final backup; deploy Laravel; apply approved migrations; verify workflows; monitor; keep Django rollback; retire after formal acceptance.

## 20. Risks and mitigations
Documented in `docs/LARAVEL_SWITCH_AUDIT.md §8` (DB-per-tenant runtime switching; session binding guard; envelope contract; append-only money semantics; stale deployment commands; PHP runtime verification; external API outage handling).

## 21. Definition of done
Laravel active backend; PostgreSQL connected; confirmed modules implemented; contracts documented; frontend integration verified; auth/authz verified; data preserved; manual verification recorded; CI without test suites; Laravel/PHP-FPM deployment; backup/rollback documented; client acceptance; Django retired only after approved cutover.

## 22. Open decisions
| Decision | Direction | Status |
|---|---|---|
| Backend Laravel | Confirmed by client (2026-11-03) | done |
| Frontend Vue/Nuxt | Keep; Next.js needs separate approval | confirmed-by-default |
| Database PostgreSQL | Confirmed | done |
| Money units | DECIMAL(12,2) implemented vs integer-minor-units plan | **open — client** |
| Auth (Sanctum) | Sessions today; evaluate Sanctum after audit | open post Stage B |
| StaffOutletAccess enforcement | Table exists, never checked | open — client |
| API prefix | Existing DRF paths vs master-plan `/api/v1` drift | open — client |
| MVP module gaps (HutBed, Reservation, RoomSeasonPrice, nightly posting, Booking, MET fetch, report computation, rental return flow) | Docs promise them; code does not have them | open — port existing only or build new? |

## 23. Project rules
1. Laravel is the selected backend direction. 2. PostgreSQL remains the database. 3. No silent frontend framework change. 4. No invented modules/roles/fields/rules. 5. Audit Django before replacing behavior. 6. Keep Django until Laravel accepted. 7. No destructive production DB operation without approval. 8. Tests in repo; never in CI. 9. Never commit secrets. 10. No production deployment/migration without approval. 11. Update this document when material decisions change.

## 24. Sources and assumptions
Scope-derived preferences (NestJS/Laravel, PostgreSQL, Ubuntu 24.04, Nginx, PHP-FPM, Leaflet/MapLibre, MET Norway) + project-context assumptions verified by the Phase 0 audit (`docs/LARAVEL_SWITCH_AUDIT.md`).

---
*Saved to repo: 2026-11-03 (work item 68, adapted from client master plan; §25 operational command reference retained in client copy — safety rules apply: never `rm -rf` Django until checks in §25.8 complete).*
