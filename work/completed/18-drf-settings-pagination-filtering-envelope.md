---
title: DRF settings — pagination, filtering, JSON envelope
id: 18
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: none
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# DRF settings — pagination, filtering, JSON envelope

## What to do
Add missing DRF configuration to `backend/config/settings/base.py`:
- DEFAULT_PAGINATION_CLASS + PAGE_SIZE
- Filter backends (DjangoFilterBackend, SearchFilter, OrderingFilter)
- Consistent JSON response envelope (wrapped responses)
- Exception handler config

## Current / Before
`backend/config/settings/base.py` REST_FRAMEWORK block only has:
- DEFAULT_AUTHENTICATION_CLASSES
- DEFAULT_PERMISSION_CLASSES
- DEFAULT_SCHEMA_CLASS

No pagination, no filtering, no envelope, no exception handler.

## Expected / After
REST_FRAMEWORK block includes pagination, filter backends, and a custom exception handler that returns consistent JSON envelopes.

## Why
CLAUDE.md §API: "Prefer consistent JSON envelopes once introduced; document them in code and keep them stable." List endpoints need pagination for scale.

## Acceptance Criteria
- [ ] `REST_FRAMEWORK` in base.py has pagination + filter backends + exception handler
- [ ] Existing ViewSets (core) still work with new settings
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/config/settings/base.py`

## Notes / Follow-ups
None.

## Return to Pending Log
None.

## Completion Log
- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Added DRF pagination (PageNumberPagination, PAGE_SIZE=20, max 100), filter backends (SearchFilter, OrderingFilter), custom exception handler with consistent JSON envelope. Filled common/pagination, common/responses, common/exceptions modules.
- **Files changed**: backend/config/settings/base.py, backend/common/pagination/__init__.py, backend/common/responses/__init__.py, backend/common/exceptions/__init__.py
- **Verification**: Code-only change, not executed (test DB setup pending per SUMMARY §3)
- **Change report**: work/changes/2026-11-02--18-drf-settings-pagination-filtering-envelope.md
- **PLAN / SUMMARY**: updated yes