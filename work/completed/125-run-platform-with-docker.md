---
title: Run platform locally with Docker Compose
id: 125
priority: medium
status: completed
milestone: none
module: infra
area: devops
effort: M
dependencies: none
bug: BUG-ENV-003
created: 2026-11-27
updated: 2026-11-27
---

# Run platform locally with Docker Compose

## What to do (What)
Run the local platform using the existing Docker Compose development configuration and verify the app is reachable.

## Current / Before
- `docker-compose.dev.yml` exists.
- The Docker Compose service state and required local configuration have not yet been checked for this run.

## Expected / After
- The Laravel API and Nuxt development server are running in Docker.
- The app is reachable at `http://lab.local.mtmtp.local:3000/` with Nuxt HMR active.
- Existing tenant data and volumes are preserved.

## Why
The user explicitly asked to run the project using Docker.

## Acceptance Criteria (complete only when all ticked)
- [x] Inspect the existing Compose file and required local environment without exposing secrets.
- [x] Inspect Nexro's SPA-dev Compose pattern and the current API container/network.
- [x] Add a Nuxt development target and dedicated SPA-dev Compose file, without invoking the base Laravel startup command.
- [x] Start the Nuxt HMR container and verify container health and `http://lab.local.mtmtp.local:3000/` in the browser.
- [x] Resolve BUG-ENV-003 and verify a source edit updates the browser without a manual reload.
- [x] Do not run migrations, reset databases, or remove volumes.
- [x] `work/changes/2026-11-27--125-docker-hmr.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `docker-compose.spa-dev.yml`
- `frontend/Dockerfile`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## Notes / Follow-ups
- Preserve local tenant data.
- Never use `docker compose down -v`.
- Use `http://lab.local.mtmtp.local:3000/` for the development SPA.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when the work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Added a Nuxt Docker development target and dedicated Compose service, enabled Vite polling for Windows bind-mounted source, and documented the required tenant host.
- **Files changed**: `frontend/Dockerfile`, `docker-compose.spa-dev.yml`, `frontend/nuxt.config.ts`, `frontend/pages/index.vue` (removed temporary HMR probe), `work/bugs/BUG-ENV-003-nuxt-docker-hmr-not-reflecting-source-edits.md`, `work/bugs/INDEX.md`, `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`.
- **Verification**: Compose config validation passed. `mtmtp_spa_dev` is running and `http://lab.local.mtmtp.local:3000/` returned HTTP 200. Browser login page rendered on the tenant host; after a source edit, HMR changed visible text without a manual reload or navigation. No migration, reset, or volume removal was run.
- **Change report**: `work/changes/2026-11-27--125-docker-hmr.md`
- **PLAN / SUMMARY**: Item 125 moved to Completed; bug and verification summaries updated.
