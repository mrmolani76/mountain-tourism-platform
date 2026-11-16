# Change Report: Redesign staff home dashboard

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 97

## 1. Summary

Replaced the old module-card home screen with an alpine "today" operations desk driven by `GET /api/reports/today`.

## 2. Before

- Generic cards: signed-in user, tenant, API health ("Django"), module count
- Marketing-style module grid linking to `/{key}`
- No live today operations data

## 3. After

- Hero band with greeting, date, tenant, refresh
- Pulse metrics: open folios, revenue, occupancy, rentals out
- Arrivals / departures panels + weather/lift chips when present
- Desk shortcuts (hotel, folio, POS, …)
- Graceful empty state when today API fails

## 4. Reason

User: complete redesign; old dashboard not acceptable.

## 5. Files Affected

- `frontend/pages/index.vue`
- `work/completed/97-…`, `work/PLAN.md`, `work/SUMMARY.md`

## 6. Verification / Status

- [ ] Not run (code-only)
- [ ] Notes: needs authenticated tenant session + reports/today available for full numbers
