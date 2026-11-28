# Change Report: Local Central and Tenant Database Reset

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Reset and re-provisioned the local project central database and registered `lab` tenant after explicit user approval.

## 2. Before
- Central database `mountain_tourism` and tenant database `lab_local` contained prior local development data.
- The local SaaS admin panel code was present; browser acceptance was pending.

## 3. After
- Recreated central application tables and applied the Saas module migrations.
- Dropped and recreated only the registered `lab_local` tenant database, then applied tenant migrations and local staff fixtures.
- Recreated the local fixture SaaS admin and central `lab` registry/domain.
- Preserved the PostgreSQL system database `postgres` and did not touch other project databases.

## 4. Reason
The user requested completing the SaaS code work first, then resetting the complete local project database including central, and said they would perform browser testing.

## 5. Files Affected
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`, and `work/todo/114-nexro-style-saas-super-admin-ui.md` — returned item 114 to Todo pending user browser acceptance.
- `work/changes/2026-11-28--114-local-central-tenant-db-reset.md` — recorded the authorized database reset.
- Database state: central `mountain_tourism`; tenant `lab_local`.

## 6. Verification / Status
- [x] Central database migrations reapplied, including Saas module migrations.
- [x] `lab_local` tenant database recreated and tenant migrations applied.
- [x] Verified one central SaaS admin, one tenant registry row, eight tenant staff rows, and the Owner fixture account.
- [x] PostgreSQL system database `postgres` preserved.
- [ ] Browser and automated tests not run; the user will perform testing.
- [ ] Work item 114 remains open pending browser acceptance and any follow-up fixes.
