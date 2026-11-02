# Hosting and CI/CD (MVP §6)

## Hard facts for agents

- **No Docker in the MVP contract.** App installs directly on the client VPS.
- **No custom domain yet.** Use free sslip.io (or similar) from server IP + Let's Encrypt, or a separate port. Later domain = Nginx + `.env` only, no code change.
- Builds happen on **GitHub Actions**, not on the VPS (memory).
- Other live sites on the same VPS must not be touched.

## Server profile

- Ubuntu 24.04 LTS, 2 CPU, 4 GB RAM, 100+ GB disk
- Node.js 22, PostgreSQL, Nginx already present
- Own Linux user, own Postgres DB/user, own internal port, own Nginx server block
- Process: PM2 or systemd (Django: gunicorn/uvicorn + systemd is fine)
- Weather + nightly posting: app scheduler or cron
- Nightly `pg_dump` backups, keep last 7, restore tested once before handover

## CI/CD

**On every PR:** install, lint, tests, build. Failed check blocks merge.

**On merge to `main`:** build on GitHub → SSH copy to new release folder → migrate → switch release → reload without downtime → health check → auto-rollback on failure. Keep last 5 releases.

SSH key in GitHub Secrets only.

Optional: test copy on another port/DB from a `develop` branch.

## Implications for this repo

- Prefer systemd + Nginx docs/scripts over Docker-first local-only workflows for Milestone 1 delivery.
- Local Docker for *developer convenience* is optional and must not be required for VPS deploy.
- `devops-agent` edits compose only if we add optional local tooling; production path is VPS + Actions.
