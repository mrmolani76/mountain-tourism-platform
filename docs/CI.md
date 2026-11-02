# CI — GitHub Actions (testing)

PDF §6: on every PR — install, lint, run tests, build. Failed check blocks merge.

## Workflow

| File | Purpose |
|------|---------|
| [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) | Backend **lint + tests** on `pull_request` and `push` to `main` |

Deploy-on-merge (SSH release, migrate, health, rollback) is **not** in this file yet — see work item `09` and `docs/HOSTING.md`.

## What the test job does

1. Starts **Postgres 16** service
2. If `backend/manage.py` is missing → **skips** (repo not scaffolded; work item 03)
3. If backend exists:
   - Python 3.12
   - `pip install` from `backend/requirements-dev.txt` or `requirements.txt`
   - `ruff check .` (when ruff installed)
   - `python manage.py check`
   - `pytest` or `manage.py test`

## Local parity

```bash
cd backend
python -m venv .venv && source .venv/bin/activate
pip install -r requirements-dev.txt   # or requirements.txt + ruff pytest pytest-django
ruff check .
python manage.py check
pytest -q
```

## Branch protection (recommended)

On GitHub → Settings → Branches → require status check:

- `Backend lint & test`

Until backend exists, the job succeeds via skip so early doc PRs are not blocked.

## Secrets

Testing job needs **no** repository secrets.  
Deploy workflow later: `SSH` key and host only in GitHub Secrets — never in the repo.

## Related todos

- `work/todo/03-m1-django-drf-scaffold.md` — makes CI actually run tests
- `work/todo/09-m1-github-actions-api.md` — full CI + deploy
- Checklist: `work/phases/01-todos/A-core-and-super-admin.md` → TODO-09
