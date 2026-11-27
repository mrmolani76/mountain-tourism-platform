---
id: BUG-ENV-003
title: Nuxt Docker HMR does not reflect source edits
status: CLOSED
type: ENV
severity: medium
created: 2026-11-27
---

# BUG-ENV-003: Nuxt Docker HMR does not reflect source edits

## 1. Summary
Source edits mounted into the Nuxt Docker development container are not reflected by hot reload or a full browser reload.

## 2. Environment
Local Windows development, Docker Compose Nuxt dev service, item 125.

## 3. Steps to Reproduce
1. Start `mtmtp_spa_dev` with `docker-compose.spa-dev.yml`.
2. Change a visible string in a mounted Nuxt page.
3. Keep the page open and wait for HMR, then reload the page.

## 4. Expected
The page updates automatically after the source edit, without a manual browser reload.

## 5. Actual
The updated source is present in the container, but the browser continues to show the previous page after waiting and reloading.

## 6. Evidence
- Before the fix, the temporary dashboard probe was present in the mounted source but was not visible after waiting 30 seconds or reloading.
- With polling enabled, Vite logged an HMR update after a mounted source edit.
- At `http://lab.local.mtmtp.local:3000/login?fresh=1`, the browser updated the login text after the source changed back, without a manual reload or frame navigation.
- The tenant-host login page rendered successfully; authentication was not attempted.

## 7. Root Cause
Vite's default file watcher did not reliably detect source changes on the Windows Docker bind mount. Enabling Vite polling for the Docker dev service made the change trigger and apply through HMR.

## 8. Workaround
Before the fix, restarting the Nuxt dev service could pick up source changes, but did not satisfy the HMR requirement.

## 9. Suggested Fix
Resolved in item 125 by enabling Vite polling only for the Docker dev service and verifying a live update at `http://lab.local.mtmtp.local:3000/`.

## 10. Impact
Local frontend development shows stale UI until the dev server is restarted. No production tenant data or money records are affected.

## 11. Related
- Work item: `work/completed/125-run-platform-with-docker.md`
- Change report: `work/changes/2026-11-27--125-docker-hmr.md`
- Other bugs: None
