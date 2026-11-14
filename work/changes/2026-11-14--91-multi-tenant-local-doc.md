# Change Report: Multi-tenant local separation doc

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 91

## 1. Summary

Added `docs/MULTI_TENANT_LOCAL.md` comparing EQMS / Nexro / Mountain Tourism central vs tenant separation. Docs only; no setup.

## 2. Before

- No single local multi-tenant separation doc
- Tenancy notes split across `DECISIONS.md`, `ARCHITECTURE.md`, Apache/Laravel setup docs

## 3. After

- `docs/MULTI_TENANT_LOCAL.md` with shared model, EQMS matrix, Nexro matrix, MTMTP target, do/do-not
- `docs/README.md` index links the new doc

## 4. Reason

User asked for a separate multi-tenant doc aligned with Nexro and EQMS; code/docs only, no Apache/MySQL/server setup.

## 5. Files Affected

- `docs/MULTI_TENANT_LOCAL.md` (new)
- `docs/README.md`
- `work/inprogress/91-multi-tenant-local-doc.md` → `work/completed/`
- `work/PLAN.md`, `work/SUMMARY.md`
- `work/changes/2026-11-14--91-multi-tenant-local-doc.md`

## 6. Verification / Status

- [x] Doc written (no runtime setup)
- [ ] Migrations/Scripts pending? N/A
- [x] Notes: setup deferred by user request
