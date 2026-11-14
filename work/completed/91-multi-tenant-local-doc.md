---
title: Multi-tenant local separation doc (EQMS + Nexro)
id: 91
priority: medium
status: completed
milestone: none
module: docs
area: docs
effort: S
dependencies: none
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Multi-tenant local separation doc

## What to do (What)

Add `docs/MULTI_TENANT_LOCAL.md` that separates central vs tenant the way EQMS and Nexro do it. Docs only. No Apache/MySQL/server setup.

## Current / Before

- Tenancy decisions live in `docs/DECISIONS.md` and `docs/ARCHITECTURE.md`
- No single doc comparing EQMS / Nexro / Mountain Tourism host+DB separation for local

## Expected / After

- `docs/MULTI_TENANT_LOCAL.md` exists
- Linked from `docs/README.md`
- No machine setup changes

## Why

User asked for a separate multi-tenant doc aligned with Nexro and EQMS; code/docs only, no setup.

## Acceptance Criteria (complete only when all ticked)

- [x] `docs/MULTI_TENANT_LOCAL.md` written
- [x] `docs/README.md` index updated
- [x] No setup scripts run
- [x] `work/changes/YYYY-MM-DD--91-*.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `docs/MULTI_TENANT_LOCAL.md`
- `docs/README.md`

## Notes / Follow-ups

- Apache EQMS DocumentRoot / `/var/www/html` shift is out of scope for this item.

---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Added MULTI_TENANT_LOCAL.md (EQMS + Nexro + MTMTP separation); linked from docs/README.md. No setup.
- **Verification**: Docs-only review; no Apache/MySQL/serve commands run for this item.
- **Change report**: `work/changes/2026-11-14--91-multi-tenant-local-doc.md`
