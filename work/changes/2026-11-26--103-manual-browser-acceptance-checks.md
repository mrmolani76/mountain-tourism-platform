# Change Report: Manual Browser Acceptance Checklist

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 103

## 1. Summary

Created a queued, ordered manual browser acceptance checklist for checking the platform module by module.

## 2. Before

- `work/todo/76-browser-e2e-testing.md` covers Playwright automation, not a manual browser walkthrough with per-step results.
- `work/PLAN.md` and `work/SUMMARY.md` did not list a separate manual walkthrough.
- Work item 104 covered the Staff listing header fix and was in progress when checklist 103 was created.

## 3. After

- Added work item 103 with ordered checks from app preflight through reports; it is now active in `work/inprogress/`.
- Initial browser preflight was blocked because the documented local SPA and API endpoints refused connections. On 2026-11-27, the user explicitly requested a local run. The existing Postgres service was started; Laravel was launched with a command override, and the existing SPA container was started directly.
- Preflight passes: direct and proxied API health return HTTP 200, the login route returns HTTP 200, and the browser renders the Mountain Tourism login page. An unauthenticated request returned HTTP 401 while the login page loaded.
- Read-only migration checks show 7 central migrations and 60 `lab_local` tenant migrations already applied. No migration or tenant reset was run during this startup.
- The Compose default Laravel command includes migration and `tenants:provision-local --reset`; it was not used. `docker compose start spa` attempted to start the old Laravel container and encountered the already allocated API port; starting only the existing SPA container succeeded.
- Browser menu review found 55 unique routes. The first pass captured titles/headings on 37, and 18 exceeded the heading wait. Follow-up confirmed `/rental` navigation links and `/buildings` table headers after waiting for async loading. The full route review remains in progress.
- Item 104 was completed and moved to `work/completed/`; item 103 remains the only WIP.

## 4. Reason

The user requested a Nexro-style todo list and browser-based checks performed one at a time.

## 5. Files Affected

- `work/inprogress/103-manual-browser-acceptance-checks.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`

## 6. Verification / Status

- [x] Item 103 is in `inprogress/`; item 104 is in `completed/`.
- [x] Kanban checker passed after normalizing the script line endings in a temporary copy; the temporary file was removed.
- [x] API direct health and SPA login: HTTP 200; proxied API health: HTTP 200; browser login page rendered.
- [x] Read-only migration status checked: central 7, tenant `lab_local` 60; no migration/reset command executed.
- [x] Running services: Postgres on 5433, API on 8010, SPA on 3000.
- [ ] Authentication and remaining module walkthrough are still in progress.
