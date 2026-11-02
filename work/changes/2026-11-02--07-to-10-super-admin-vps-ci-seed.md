# Change Report: Items 07-10 — Super admin, VPS setup, CI deploy, Foundation seed

- **Date**: 2026-11-02
- **By**: coding agent
- **Change ID**: items 07-10 (Phase 1 Module A)

## 1. Summary

Completed Phase 1 Module A remaining items: super admin APIs (complex + owner + default settings), VPS setup guide, GitHub Actions deploy workflow, and foundation seed command.

## 2. Before

- Item 07: `register_complex` created Tenant + Domain only (no owner, no settings).
- Item 08: No VPS setup guide.
- Item 09: Only PR testing CI existed; no deploy workflow.
- Item 10: No seed command.

## 3. After

### Item 07 — Super admin APIs
- `register_complex` now creates owner Staff + default TenantSettings on tenant DB (atomic transaction).
- `TenantCreateSerializer` accepts optional `owner_email` + `owner_password`.
- `TenantViewSet.create` passes owner credentials through.
- Activate/deactivate already existed; inactive tenant blocks staff (middleware + auth).
- Non-super → 403 via `IsSaasUser` permission.

### Item 08 — VPS setup guide
- Created `docs/VPS_SETUP.md`: Linux user, Postgres, Python venv, .env, Nginx, systemd, migrate, SSL, backups.
- No Docker path documented. Domain-later = Nginx + env only.

### Item 09 — GitHub Actions deploy
- Created `.github/workflows/deploy.yml`: build on GitHub → SSH copy → migrate → switch → reload → health check → rollback.
- Testing CI (`ci.yml`) already existed (PR lint/test).
- No secrets in testing workflow.

### Item 10 — Foundation seed
- Created `backend/modules/core/management/commands/seed_foundation.py`.
- Seeds 6 buildings (Valley 1050, Hotel 1200, Chalets 1250, Mid 1800, Hut 2100, Summit 2600) + sample outlets.
- Idempotent (`get_or_create`), `--reset` flag for clean slate.
- Dev-only safety noted in docstring.

## 4. Files Affected

- `backend/modules/saas/services/tenant.py` — register_complex updated
- `backend/modules/saas/serializers/tenant.py` — owner_email/owner_password fields
- `backend/modules/saas/views/api.py` — TenantViewSet.create updated
- `docs/VPS_SETUP.md` — new
- `.github/workflows/deploy.yml` — new
- `backend/modules/core/management/commands/seed_foundation.py` — new
- `work/PLAN.md`, `work/SUMMARY.md`, `work/bugs/INDEX.md` — kanban sync
- `work/completed/07-*.md`, `08-*.md`, `09-*.md`, `10-*.md` — moved from todo

## 5. Verification
- py_compile on all touched Python files: OK
- kanban check: passed
- Test suite: code-only (runs on setup machine)