---
name: verify-before-done
description: >-
  MUST activate before claiming "done", "finished", "ready for review", or
  similar. Runs the done checklist and refuses to declare done if required
  verification and change report are missing.
---

# Verify Before Done

## Checklist

### 1. Request
- [ ] Each explicit ask addressed

### 2. Files
- [ ] List created/modified/deleted
- [ ] No unintended changes (`git diff --name-only`)

### 3. Tests (when verification allowed)
- [ ] Affected tests run with real results
- [ ] Failures → `triage-before-fix`

### 4. Non-negotiables if touched

| Area | Checked |
|------|---------|
| Tenant scope | [ ] Yes / [ ] N/A |
| Money append-only / voids | [ ] Yes / [ ] N/A |
| Role / outlet authz | [ ] Yes / [ ] N/A |
| Domain vocabulary names | [ ] Yes / [ ] N/A |
| MVP scope boundary | [ ] Yes / [ ] N/A |

### 5. Reports
- [ ] `work/changes/YYYY-MM-DD--*.md` written
- [ ] Work MD moved to `completed/` if tracked
- [ ] SUMMARY/PLAN updated

### 6. Git
- [ ] Asked **"Commit aur push karun?"**
- [ ] No commit/push without explicit yes

## Refusal

Do not declare done without change report, or when touched money/tenant behavior has no verification story.
