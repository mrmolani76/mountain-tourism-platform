# Change Report: Manual Browser Acceptance Checklist

- **Date**: 2026-11-26
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
- Initial browser preflight was blocked because the documented local SPA and API endpoints refused connections. User later confirmed Docker was started, but inspection found all three containers stopped. User then authorized the agent to start the stack; the existing Postgres container was started and Laravel/SPA were launched without the compose startup reset command.
- Preflight now passes: API health responds with database `ok`, login renders, and proxied health responds.
- Browser menu review found 55 unique routes. The first pass captured titles/headings on 37, and 18 exceeded the heading wait. Follow-up confirmed `/rental` navigation links and `/buildings` table headers after waiting for async loading. The full route review remains in progress.
- Item 104 was completed and moved to `work/completed/`; item 103 remains the only WIP.

## 4. Reason

The user requested a Nexro-style todo list and browser-based checks performed one at a time.

## 5. Files Affected

- `work/todo/103-manual-browser-acceptance-checks.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`

## 6. Verification / Status

- [x] Item 103 is in `inprogress/`; item 104 is in `completed/`.
- [ ] `bash scripts/check-work-kanban.sh` could not run successfully. Bash rejected the script's `set -o pipefail` line (`pipefail\r`).
- [x] Before startup: `docker compose ps -a` showed `mtmtp_db`, `mtmtp_spa`, and `mtmtp_laravel` all exited. Laravel's compose command includes local tenant `--reset`; it was not run during startup.
- [ ] No application tests, builds, linters, installs, or infrastructure commands run for this documentation change.
