# Change Report: DRF settings — pagination, filtering, JSON envelope

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 18

## 1. Summary
Added missing DRF configuration: pagination, filter backends, custom exception handler with consistent JSON envelope, plus shared common modules for pagination, responses, and exceptions.

## 2. Before
`backend/config/settings/base.py` REST_FRAMEWORK block only had:
- DEFAULT_AUTHENTICATION_CLASSES
- DEFAULT_PERMISSION_CLASSES
- DEFAULT_SCHEMA_CLASS

No pagination, no filtering, no envelope, no custom exception handler.
`common/pagination/`, `common/responses/`, `common/exceptions/` were empty stubs.

## 3. After
- `backend/config/settings/base.py` REST_FRAMEWORK now includes:
  - DEFAULT_PAGINATION_CLASS = common.pagination.StandardResultsSetPagination (PAGE_SIZE=20, max 100)
  - DEFAULT_FILTER_BACKENDS = SearchFilter, OrderingFilter
  - EXCEPTION_HANDLER = common.responses.custom_exception_handler
- `backend/common/pagination/__init__.py` — StandardResultsSetPagination with envelope-wrapped paginated responses
- `backend/common/responses/__init__.py` — success_response(), error_response(), custom_exception_handler() with consistent JSON envelope
- `backend/common/exceptions/__init__.py` — DomainError, MoneyAppendOnlyError, TenantIsolationError, CapacityConflictError

## 4. Reason
CLAUDE.md §API: "Prefer consistent JSON envelopes once introduced; document them in code and keep them stable." List endpoints need pagination for scale. Error responses should be consistent across all DRF endpoints.

## 5. Files Affected
- `backend/config/settings/base.py`
- `backend/common/pagination/__init__.py` (new content)
- `backend/common/responses/__init__.py` (new content)
- `backend/common/exceptions/__init__.py` (new content)

## 6. Verification / Status
- [ ] Not executed — code-only change (test DB setup pending, per SUMMARY §3)
- [ ] No migrations needed
- [ ] Existing core ViewSets inherit pagination + filters from defaults automatically
- [ ] Change report written: this file
- [ ] PLAN.md + SUMMARY.md updated (item 18 moved Todo → Completed)
