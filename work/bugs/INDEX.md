# Bug Index

## Open

| ID | Title | Severity | Status | Created |
|----|-------|----------|--------|---------|
| BUG-TENANT-002 | SaaS onboarding does not provision tenant database or email Owner credentials | high | RETESTING | 2026-11-28 |
| BUG-AUTH-001 | Owner provisioning can overwrite an existing staff role | high | IN_PROGRESS | 2026-11-27 |
| BUG-APP-011 | Static Core menu fallback omits Outlets | medium | BUG_CREATED | 2026-11-27 |
| BUG-APP-010 | Outlets listing shows building ID instead of name | medium | RETESTING | 2026-11-27 |
| BUG-APP-006 | Dev hard refresh drops :3000 (Apache :80 / login 502) | high | BUG_CREATED | 2026-11-06 |

## Verified

| ID | Title | Severity | Status | Verified |
|----|-------|----------|--------|----------|
| BUG-AUTH-002 | SaaS login fails when CSRF and login requests exceed the frontend timeout | high | VERIFIED | 2026-11-28 |
| BUG-DATA-001 | Central SaaS dashboard fails when tenant UUID column is missing | high | VERIFIED | 2026-11-28 |
| BUG-ENV-004 | Nuxt dev server retains stale SaaS route imports | medium | VERIFIED | 2026-11-28 |
| BUG-APP-012 | SaaS login route stays blank after session probe | high | VERIFIED | 2026-11-27 |
| BUG-APP-009 | Owner dashboard today report returns HTTP 500 | medium | VERIFIED | 2026-11-27 |
| BUG-APP-008 | Staff detail URLs use numeric IDs instead of UUIDs | medium | VERIFIED | 2026-11-27 |
| BUG-API-002 | Tenant validation calls an unsupported connection method | high | VERIFIED | 2026-11-27 |
| BUG-ENV-002 | Todo notifier fails with Windows CRLF checkout | medium | VERIFIED | 2026-11-27 |
| BUG-API-001 | Core listing config shadowed — Unknown listing resource 404 | high | VERIFIED | 2026-11-16 |
| BUG-APP-005 | Domain ViewSets use empty authentication_classes (auth bypass risk) | high | VERIFIED | 2026-11-03 |
| BUG-APP-004 | Staff login bad credentials → 403 instead of 401 | low | VERIFIED | 2026-11-03 |
| BUG-APP-003 | Season overlap rejected same-kind only | medium | VERIFIED | 2026-11-03 |
| BUG-APP-002 | All list endpoints 500 — pagination AttributeError | high | VERIFIED | 2026-11-03 |
| BUG-APP-001 | Prod SPA blank screen — Nuxt modules not registered | high | VERIFIED | 2026-11-03 |
| BUG-TENANT-001 | Cross-host session identity swap risk | medium | VERIFIED | 2026-11-02 |
| BUG-ENV-001 | Tenant DB template clone missing OPTIONS key | high | VERIFIED | 2026-11-02 |

## Closed

| ID | Title | Severity | Status | Closed |
|----|-------|----------|--------|--------|
| BUG-ENV-003 | Nuxt Docker HMR does not reflect source edits | medium | CLOSED | 2026-11-27 |
| BUG-MONEY-001 | Generic folio void defaults to weather reason | medium | CLOSED | 2026-11-27 |
| BUG-APP-007 | Staff listing table renders without column header row | medium | CLOSED | 2026-11-26 |
