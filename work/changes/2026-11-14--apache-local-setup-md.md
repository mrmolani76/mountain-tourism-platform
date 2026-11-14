# Change Report: Apache local setup guide MD

- **Date**: 2026-11-14
- **By**: Agent

## 1. Summary

Added Complere-style Apache local setup documentation. Docs only; no machine
config and no Docker changes.

## 2. Before

- Local paths documented mainly via Docker (`DOCKER_SETUP.md`) and artisan serve
  (`LARAVEL_LOCAL_SETUP.md` / `frontend/docs/LOCAL_SETUP.md`).
- No dedicated Apache vhost + hosts + remove checklist.

## 3. After

- `docs/APACHE_LOCAL_SETUP.md` with hosts, example vhosts (`mtmtp-*`), env notes,
  verify curls, and machine-remove steps (only when human asks).
- Linked from `docs/README.md`.

## 4. Reason

User: pehle proper MD; Apache setup like Complere; Docker touch mat karo;
machine se remove sirf jab kahu.

## 5. Files Affected

- `docs/APACHE_LOCAL_SETUP.md`
- `docs/README.md`

## 6. Verification / Status

- [x] Docs only (no `a2ensite`, no hosts edit, no Docker edits)
