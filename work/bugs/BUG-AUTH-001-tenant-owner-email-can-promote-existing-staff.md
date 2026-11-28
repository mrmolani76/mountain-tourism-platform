---
id: BUG-AUTH-001
title: Owner provisioning can overwrite an existing staff role
status: IN_PROGRESS
type: AUTH
severity: high
created: 2026-11-27
---

# BUG-AUTH-001: Owner Provisioning Can Overwrite an Existing Staff Role

## 1. Summary
Tenant Owner provisioning uses `updateOrCreate` by email and overwrites the matching staff role with `owner`, which can silently promote an existing non-owner account.

## 2. Environment
Laravel SaaS tenant onboarding, central Super Admin flow.

## 3. Steps to Reproduce
1. Prepare a tenant database containing an active Manager or other staff account.
2. Call tenant Owner provisioning with the existing staff email.
3. Allow `TenantService::createOwnerStaff()` to run.

## 4. Expected
Owner provisioning must not change an existing non-owner staff role or password without an explicit, safe workflow.

## 5. Actual
`updateOrCreate` matches the email and updates the existing account's password, role, and active state to Owner.

## 6. Evidence
- `backend-laravel/Modules/Saas/App/Services/TenantService.php` calls `Staff::on($conn)->updateOrCreate(['email' => ...], [... 'role' => StaffRole::OWNER ...])`.

## 7. Root Cause
Owner creation and existing-staff update are combined into one upsert keyed only by email.

## 8. Workaround
Do not use an email that already belongs to a non-owner staff account for initial Owner setup.

## 9. Suggested Fix
Create a new Owner only when the email is unused; otherwise return a conflict without changing the existing account.

## 10. Impact
An onboarding action can unexpectedly replace an existing staff member's role and password.

## 11. Related
- Work item: `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- Change report: Pending
- Other bugs: None
