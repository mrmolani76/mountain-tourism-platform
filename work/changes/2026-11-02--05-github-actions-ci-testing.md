# Change Report: GitHub Actions CI testing workflow

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 05

## 1. Summary
Added GitHub Actions workflow for backend lint + tests (Postgres service). Skips until Django scaffold exists. Docs in `docs/CI.md`.

## 2. Before
No `.github/workflows/`.

## 3. After
- `.github/workflows/ci.yml` — PR/push CI testing
- `docs/CI.md` — how it works + branch protection note
- Links from docs README / HOSTING; todo 09 PR testing checkbox marked

## 4. Reason
User asked for CI/CD GitHub workflow for testing.

## 5. Files Affected
- `.github/workflows/ci.yml`
- `docs/CI.md`, `docs/README.md`, `docs/HOSTING.md`
- `work/todo/09-m1-github-actions-api.md`
- This report

## 6. Verification / Status
- [ ] Real lint/test run needs work item 03 (`backend/manage.py`)
- [x] Workflow YAML added; no secrets in testing job
- [ ] Deploy-on-main still open on todo 09
