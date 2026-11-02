---
title: Backend structure gaps fill (pyproject, common, tests split, admin, env stub)
id: 16
priority: high
status: completed
milestone: 1
module: infra
area: backend
effort: M
dependencies: 12
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Backend structure gaps fill

**Source doc**: [`docs/BACKEND_STRUCTURE_GAPS.md`](../../docs/BACKEND_STRUCTURE_GAPS.md) (gap audit, item 15)

## What to do (What)
Fill the physical tree gaps listed in the gaps doc, per its §6 suggested fill order: pyproject.toml + requirements files (dependencies work), common/ scaffold, tests split into unit/integration/api, per-module admin.py, root .env.example stub.

## Current / Before
Per gaps doc §3: no `backend/pyproject.toml`, no `uv.lock`, no `backend/common/` tree, `backend/tests/` flat (4 test files), no per-module `admin.py` (10 modules), no root `.env.example`. CI workflow expects `backend/requirements-dev.txt` which does not exist in repo.

## Expected / After
- `backend/pyproject.toml` declared deps + tool config aligned with `backend/DEPENDENCIES.md`
- `backend/requirements.txt` + `backend/requirements-dev.txt` mirror pyproject (CI-compatible)
- `backend/common/{exceptions,pagination,responses,utilities,constants}/` empty packages with rule docstrings
- `backend/tests/{unit,integration,api}/` split with existing test files relocated
- `backend/modules/<mod>/admin.py` for saas, core, hotel, folio, pos, rental, services, transport, weather, reports (empty, MVP-optional)
- Root `/.env.example` stub pointing at `backend/.env.example`
- Gaps doc checklist annotated with fill status
- `uv.lock` stays for the setup machine (code-only policy)

## Why
Gap audit item 15 listed these as structural gaps; CI workflow currently cannot find requirements files.

## Acceptance Criteria (complete only when all ticked)
- [x] `python3 manage.py check` clean
- [x] `makemigrations --check` clean
- [x] py_compile on new files
- [x] Test files still discoverable (import-level validation)
- [x] `work/changes/2026-11-02--16-backend-structure-gaps-fill.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/pyproject.toml`, `backend/requirements*.txt`, `backend/common/**`, `backend/tests/**`, `backend/modules/*/admin.py`, `/.env.example`, `docs/BACKEND_STRUCTURE_GAPS.md`

## Notes / Follow-ups
- Code-only machine: no uv sync, no installs, no test execution (setup machine later).
- Optional renames (`local.py`→`development.py`, `prod.py`→`production.py`) deliberately skipped (doc §3.6 says not blockers).

---

## Return to Pending Log (when work stops midway)

Repeatable block (newest first):

### YYYY-MM-DD
- **Returned by**:
- **Reason**:
- **Next step**:

---

## Completion Log (when work is complete)

- **Completed by**: Buffy (coding agent)
- **Date**: 2026-11-02
- **What was done**: Chalier gaps doc ke §6 order me sabhi physical gaps fill kiye: pyproject.toml + requirements.txt/requirements-dev.txt (CI-compatible), common/ package scaffold, test split (unit/integration/api), 10 modules me admin.py, aur root .env.example stub. Gaps doc mein bhi fill status ki notes display hain; aur uv.lock ab bhi setup machine ke liye reserved hai.
- **Files changed**: `backend/pyproject.toml`, `backend/requirements*.txt`, `backend/config/settings/__init__.py`, `backend/common/**`, `backend/tests/**` (nayi subpackages ke saath), `backend/modules/*/admin.py` (10), `/.env.example`, `docs/BACKEND_STRUCTURE_GAPS.md`, kanban files.
- **Verification**: `manage.py check` 0 issues; `makemigrations core saas --check` clean; `compileall` common+config+modules+tests OK; chaaron test modules import cleanly test kiye.
- **Change report**: `work/changes/2026-11-02--16-backend-structure-gaps-fill.md`
- **PLAN / SUMMARY**: updated yes
