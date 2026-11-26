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

- Added work item 103 in `work/todo/` with ordered checks from app preflight through reports.
- Added item 103 to the Todo table in `work/PLAN.md` and the Todo section in `work/SUMMARY.md`.
- After user authorized pausing item 104, moved item 104 back to `todo/` and attempted to pick up item 103.
- Initial browser preflight was blocked because the documented local SPA and API endpoints refused connections. User later confirmed Docker was started, so item 103 was picked up again. The retry still failed: SPA URLs returned `ERR_FAILED`, and API health URLs refused connections.
- Updated `work/MEMORY.md`, PLAN, and SUMMARY to show no active WIP and the blocked next step.

## 4. Reason

The user requested a Nexro-style todo list and browser-based checks performed one at a time.

## 5. Files Affected

- `work/todo/103-manual-browser-acceptance-checks.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-26--103-manual-browser-acceptance-checks.md`

## 6. Verification / Status

- [x] Item 103 was retried after user confirmed Docker was started, then returned to `todo/` after the browser preflight failed again; item 104 remains in `todo/`.
- [ ] `bash scripts/check-work-kanban.sh` could not run successfully. Bash rejected the script's `set -o pipefail` line (`pipefail\r`).
- [ ] Browser preflight blocked: SPA `http://lab.local.mtmtp.local:3000/` and `http://localhost:3000/` returned `ERR_FAILED`; API `http://127.0.0.1:8010/api/health/` and `http://127.0.0.1:8000/api/health/` refused connections. No Docker commands were run.
- [ ] No application tests, builds, linters, installs, or infrastructure commands run for this documentation change.
