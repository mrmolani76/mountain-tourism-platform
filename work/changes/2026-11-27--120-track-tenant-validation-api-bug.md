# Change Report: Work 120 — Track Tenant Validation API Bug

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 120-tenant-validation-api-bug

## 1. Summary
Recorded BUG-API-002 and created TODO 120 for unsupported tenant-scoped Laravel validation rule builders.

## 2. Before
- The focused item 106 test run failed during Hotel and Folio request validation with `Call to undefined method Illuminate\Validation\Rules\Exists::connection()`.
- No bug report or work item tracked this cross-module validation failure.

## 3. After
- Added `work/bugs/BUG-API-002-tenant-validation-connection-method.md` and an Open/BUG_CREATED row in the bug index.
- Added TODO 120 for replacing unsupported `exists`/`unique` connection calls with supported tenant-qualified validation rules.
- Updated PLAN and SUMMARY. Item 106 remains the only in-progress work item; 120 is pending.
- No production fix for BUG-API-002 was made.

## 4. Reason
The test failure exposed a confirmed request-validation error affecting multiple module helpers. It must be tracked separately from the folio void reason fix.

## 5. Files Affected
- `work/bugs/BUG-API-002-tenant-validation-connection-method.md`
- `work/bugs/INDEX.md`
- `work/todo/120-fix-tenant-scoped-validation-rules.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-27--120-track-tenant-validation-api-bug.md`

## 6. Verification / Status
- [x] Confirmed the runtime error in the focused Hotel and Folio request paths.
- [x] Confirmed analogous helper calls across eight modules.
- [x] Item 106 remains the sole active Kanban item; TODO 120 is not active.
- [ ] BUG-API-002 fix and verification remain pending.
