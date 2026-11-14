# Change Report: Apache local install/remove scripts

- **Date**: 2026-11-14
- **By**: Agent

## 1. Summary

Added host install/remove scripts and Apache conf examples for Complere-style
local hosting. Agent environment cannot write `/etc`; human runs install with sudo.

## 2. Before

- Docs-only `docs/APACHE_LOCAL_SETUP.md` with inline examples.
- No `mtmtp-*` conf files or install/remove scripts in repo.

## 3. After

- `docs/apache/mtmtp-lab.localhost.conf`
- `docs/apache/mtmtp-saas.localhost.conf`
- `scripts/apache-local-install.sh`
- `scripts/apache-local-remove.sh`
- Guide updated with `sudo bash scripts/apache-local-install.sh`
- Created local gitignored `.env` files for Laravel + Nuxt (not committed)

## 4. Reason

User: `setup karo`. Cursor agent sandbox cannot modify host `/etc/apache2` or
`/etc/hosts` (permission denied / read-only). Scripts let the host apply setup.

## 5. Files Affected

- `docs/apache/*`
- `scripts/apache-local-install.sh`
- `scripts/apache-local-remove.sh`
- `docs/APACHE_LOCAL_SETUP.md`
- local `.env` (gitignored)

## 6. Verification / Status

- [x] Scripts + confs in repo
- [ ] Host: `sudo bash scripts/apache-local-install.sh` (needs human on host)
- [ ] `php artisan serve` + `npm run dev` after install
