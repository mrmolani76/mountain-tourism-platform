# Change Report: EQMS-style modules layout

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 12

## 1. Summary
Domain code moved from `backend/apps/` to Complere-like `backend/modules/{Name}/` with shared internal layout + `module.json`.

## 2. Before
- Flat `backend/apps/{saas,core,hotel,...,services_app,...}`
- Imports `apps.*`

## 3. After
- `backend/modules/{saas,core,hotel,folio,pos,rental,services,transport,weather,reports}/`
- Each: `models/`, `services/`, `serializers/`, `views/`, `permissions/`, `urls.py`, `migrations/`, `module.json`
- `tenancy/` remains platform (Complere `app/` role)
- Imports `modules.*`; INSTALLED_APPS updated
- Guide: `backend/modules/README.md`

## 4. Reason
User: module-wise like eQMS Complere.

## 5. Files Affected
- `backend/modules/**` (renamed from apps)
- `backend/config/settings/base.py`, `db_router.py`, `urls.py`
- `backend/tests/**`
- `docs/ARCHITECTURE.md`, `docs/DECISIONS.md`, `docs/modules/*`, `CLAUDE.md`, coding-agent skill

## 6. Verification / Status
- [ ] Tested / Verified? No — code only
- [x] Notes: historical completed work MDs still mention `apps.*` (left as history)
