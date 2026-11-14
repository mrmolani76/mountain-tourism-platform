# Change Report: Module I Owner dashboard and reports

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 89

## 1. Summary

Shipped `Modules/Reports` Today dashboard + date-range revenue/weather
cancellation reports with CSV, plus Nuxt `/reports` UI and menu.

## 2. Before

- Modules A–H product APIs + staff FE largely present.
- No owner Today aggregates or date-range report/CSV endpoints.

## 3. After

- `api/reports/today` and revenue/weather-cancellation range APIs + `.csv`.
- Spatie Data `ReportRangeData` validation (no Form Requests).
- Integer cents; non-voided charges/payments only.
- Nuxt hub, today, revenue, weather-cancellations pages; Core menu + nav.
- `report_saved_view` skipped (optional schema).

## 4. Reason

User: PDF check showed Module I remaining; start work to close Milestone 5 product gap.

## 5. Files Affected

- `backend-laravel/Modules/Reports/**`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/modules_statuses.json`
- `backend-laravel/Modules/Core/config/menu.php`
- `frontend/composables/api-clients/useReportsApiClient.ts`
- `frontend/pages/reports/**`
- `frontend/utils/app-modules.ts`
- work kanban 89

## 6. Verification / Status

- [x] `php -l` clean on Reports PHP
- [x] Code-only (no migrate/tests/npm)
- [ ] Cent-accuracy feature tests deferred
- Notes: lobby TV full page still Phase 5 leftover; Hotel 72 setup verify still open.
