---
title: Module I — Owner dashboard and reports (Laravel + Nuxt)
id: 89
priority: high
status: completed
milestone: 5
module: I-dashboard
area: backend
effort: L
dependencies: 88
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Module I — Owner dashboard and reports

## What to do (What)

PDF §5I / Phase 5: `Modules/Reports` — Today dashboard aggregates + date-range
reports (outlet / service / payment method / weather cancellations) + CSV.
Staff Nuxt screens. Spatie Data DTOs (eQMS Complere), no Form Requests.

## Acceptance Criteria

- [x] Reports module scaffold + provider (Spatie Data validation)
- [x] Today: occupancy, arrivals/departures, revenue by outlet, rentals out, open folios, lifts, weather warnings
- [x] Range: revenue by outlet / service / payment method; weather cancellations; CSV
- [x] Nuxt reports UI + menu
- [x] Code-only; change report + PLAN/SUMMARY

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Reports module Today + range APIs + CSV; Nuxt reports pages; menu/nav; Spatie Data only
- **Files changed**: `Modules/Reports/**`, providers, menu, `frontend/pages/reports/**`, `useReportsApiClient.ts`, `app-modules.ts`
- **Verification**: code-only; php -l clean
- **Change report**: `work/changes/2026-11-14--89-laravel-reports-module-i.md`
- **PLAN / SUMMARY**: synced
